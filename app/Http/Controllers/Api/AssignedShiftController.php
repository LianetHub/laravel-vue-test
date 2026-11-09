<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignedShiftRequest;
use App\Http\Requests\UpdateAssignedShiftRequest;
use App\Http\Resources\AssignedShiftResource;
use App\Models\AssignedShift;
use App\Models\Schedule;
use App\Services\ScheduleMutation;
use App\Services\ScheduleSummary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignedShiftController extends Controller
{
    public function index(Request $request, Schedule $schedule): AnonymousResourceCollection
    {
        $request->validate([
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => array_filter(['sometimes', 'date_format:Y-m-d', $request->filled('from') ? 'after_or_equal:from' : null]),
        ]);
        $query = $schedule->assignedShifts()->orderBy('date')->orderBy('start_time');

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        return AssignedShiftResource::collection($query->get());
    }

    public function store(StoreAssignedShiftRequest $request, Schedule $schedule): AssignedShiftResource
    {
        $shift = DB::transaction(function () use ($schedule, $request) {
            ScheduleMutation::lock($schedule->id);
            $data = $request->validated();
            ScheduleMutation::assertAvailable($schedule->id, (int) $data['employee_id'], $data['date']);
            $shift = $schedule->assignedShifts()->create($data);
            ScheduleSummary::refresh($schedule);

            return $shift;
        });

        return new AssignedShiftResource($shift);
    }

    public function update(UpdateAssignedShiftRequest $request, AssignedShift $shift): AssignedShiftResource
    {
        DB::transaction(function () use ($shift, $request) {
            ScheduleMutation::lock($shift->schedule_id);
            $shift->refresh();
            $data = $request->validated();
            ScheduleMutation::assertAvailable($shift->schedule_id, (int) ($data['employee_id'] ?? $shift->employee_id),
                $data['date'] ?? $shift->date->format('Y-m-d'), $shift->id);
            if (substr($data['start_time'] ?? $shift->start_time, 0, 5) >= substr($data['end_time'] ?? $shift->end_time, 0, 5)) {
                throw ValidationException::withMessages(['end_time' => 'Время окончания должно быть позже времени начала.']);
            }
            $shift->update($data);
            ScheduleSummary::refresh($shift->schedule);
        });

        return new AssignedShiftResource($shift->fresh());
    }

    public function destroy(AssignedShift $shift): JsonResponse
    {
        DB::transaction(function () use ($shift) {
            ScheduleMutation::lock($shift->schedule_id);
            $schedule = $shift->schedule;
            $shift->delete();
            ScheduleSummary::refresh($schedule);
        });

        return response()->json(null, 204);
    }
}
