<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\SyncScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\AssignedShift;
use App\Models\Employee;
use App\Models\Schedule;
use App\Services\ScheduleMutation;
use App\Services\ScheduleSummary;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ScheduleResource::collection(
            Schedule::query()->orderByDesc('updated_at')->get()
        );
    }

    public function store(StoreScheduleRequest $request): ScheduleResource
    {
        $schedule = Schedule::query()->create([
            'title' => $request->validated('title'),
            'address' => $request->validated('address'),
            'status' => 'draft',
            'employees_count' => Employee::query()->count(),
            'progress' => 0,
            'period_end' => null,
        ]);

        return new ScheduleResource($schedule);
    }

    public function show(Schedule $schedule): ScheduleResource
    {
        return new ScheduleResource($schedule);
    }

    public function update(SyncScheduleRequest $request, Schedule $schedule): ScheduleResource
    {
        return $this->sync(
            $schedule,
            $request->validated('status') ?? $schedule->status,
            $request->validated('shifts')
        );
    }

    public function save(SyncScheduleRequest $request, Schedule $schedule): ScheduleResource
    {
        return $this->sync($schedule, 'draft', $request->validated('shifts'));
    }

    public function publish(SyncScheduleRequest $request, Schedule $schedule): ScheduleResource
    {
        return $this->sync($schedule, 'published', $request->validated('shifts'));
    }

    private function sync(Schedule $schedule, string $status, array $shifts): ScheduleResource
    {
        DB::transaction(function () use ($schedule, $status, $shifts) {
            ScheduleMutation::lock($schedule->id);
            $schedule->update(['status' => $status]);

            $keepIds = [];

            foreach ($shifts as $payload) {
                $data = [
                    'employee_id' => $payload['employee_id'],
                    'template_id' => $payload['template_id'] ?? null,
                    'date' => $payload['date'],
                    'start_time' => $payload['start_time'],
                    'end_time' => $payload['end_time'],
                    'color' => $payload['color'] ?? null,
                ];

                if (! empty($payload['id'])) {
                    $shift = AssignedShift::query()
                        ->where('schedule_id', $schedule->id)
                        ->where('id', $payload['id'])
                        ->first();

                    if ($shift) {
                        $shift->update($data);
                        $keepIds[] = $shift->id;

                        continue;
                    }
                    throw ValidationException::withMessages(['shifts' => 'Одна из смен была удалена. Обновите график перед сохранением.']);
                }

                $created = $schedule->assignedShifts()->create($data);
                $keepIds[] = $created->id;
            }

            $deleteQuery = $schedule->assignedShifts();
            if (count($keepIds) > 0) {
                $deleteQuery->whereNotIn('id', $keepIds);
            }
            $deleteQuery->delete();

            ScheduleSummary::refresh($schedule);
        });

        return new ScheduleResource($schedule->fresh()->load('assignedShifts'));
    }
}
