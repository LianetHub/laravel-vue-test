<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Schedule;
use App\Models\ShiftTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;

    private Schedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->employee = Employee::create(['first_name' => 'Анна', 'last_name' => 'Иванова', 'position' => 'агент']);
        $this->schedule = Schedule::create(['title' => 'Тест', 'status' => 'draft']);
    }

    private function shift(array $overrides = []): array
    {
        return array_replace([
            'employee_id' => $this->employee->id,
            'template_id' => null,
            'date' => '2026-01-31',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'color' => 'brand',
        ], $overrides);
    }

    public function test_save_returns_persisted_snapshot_and_publish_updates_status(): void
    {
        $url = '/api/schedules/'.$this->schedule->id;
        $saved = $this->postJson($url.'/save', ['shifts' => [$this->shift()]])
            ->assertOk()->assertJsonPath('data.status', 'draft')
            ->assertJsonCount(1, 'data.assigned_shifts')->assertJsonPath('data.progress', 100);
        $shifts = $saved->json('data.assigned_shifts');
        $this->assertNotNull($shifts[0]['id']);
        $this->postJson($url.'/publish', ['shifts' => $shifts])->assertOk()->assertJsonPath('data.status', 'published');
        $this->postJson($url.'/publish', ['shifts' => $shifts])->assertOk();
        $this->assertDatabaseCount('assigned_shifts', 1);
        $this->getJson($url.'/shifts')->assertJsonPath('data.0.start_time', '09:00');
        $this->postJson($url.'/save', ['shifts' => []])->assertOk()->assertJsonCount(0, 'data.assigned_shifts')
            ->assertJsonPath('data.status', 'draft')->assertJsonPath('data.progress', 0)->assertJsonPath('data.period_end', null);
        $this->assertDatabaseCount('assigned_shifts', 0);
    }

    public function test_malformed_payloads_return_422(): void
    {
        foreach ([null, 'invalid', 42, [null], ['bad']] as $value) {
            $this->postJson('/api/schedules/'.$this->schedule->id.'/save', ['shifts' => $value])->assertUnprocessable();
        }
        $this->postJson('/api/schedules/'.$this->schedule->id.'/save', [])->assertUnprocessable();
    }

    public function test_invalid_shift_values_are_rejected_atomically(): void
    {
        $original = $this->schedule->assignedShifts()->create($this->shift());
        foreach (
            [
                ['start_time' => ''],
                ['end_time' => '08:00'],
                ['end_time' => '09:00'],
                ['start_time' => '25:00'],
                ['start_time' => ['09:00']],
                ['employee_id' => 99999],
                ['template_id' => 99999],
                ['date' => '2026-02-30'],
            ] as $invalid
        ) {
            $this->postJson('/api/schedules/'.$this->schedule->id.'/publish', ['shifts' => [$this->shift($invalid)]])
                ->assertUnprocessable();
            $this->assertSame('draft', $this->schedule->fresh()->status);
            $this->assertDatabaseHas('assigned_shifts', ['id' => $original->id, 'start_time' => '09:00']);
            $this->assertDatabaseCount('assigned_shifts', 1);
        }
    }

    public function test_sync_rejects_duplicate_cells_and_ids_and_foreign_ids(): void
    {
        $shift = $this->schedule->assignedShifts()->create($this->shift());
        $other = Schedule::create(['title' => 'Другой', 'status' => 'draft']);
        $foreign = $other->assignedShifts()->create($this->shift());
        $url = '/api/schedules/'.$this->schedule->id.'/save';
        $this->postJson($url, ['shifts' => [$this->shift(), $this->shift()]])->assertUnprocessable();
        $this->postJson($url, ['shifts' => [
            $this->shift(['id' => $shift->id]),
            $this->shift(['id' => $shift->id, 'date' => '2026-02-01']),
        ]])->assertUnprocessable();
        foreach ([$foreign->id, 99999] as $id) {
            $this->postJson($url, ['shifts' => [$this->shift(['id' => $id])]])->assertUnprocessable();
        }
        $this->assertDatabaseCount('assigned_shifts', 2);
    }

    public function test_crud_updates_summary_and_prevents_occupied_destination(): void
    {
        $url = '/api/schedules/'.$this->schedule->id.'/shifts';
        $first = $this->postJson($url, $this->shift())->assertCreated()->json('data.id');
        $this->assertSame(100, $this->schedule->fresh()->progress);
        $this->postJson($url, $this->shift())->assertUnprocessable();
        $second = $this->postJson($url, $this->shift(['date' => '2026-02-01']))->assertCreated()->json('data.id');
        $this->putJson('/api/shifts/'.$second, ['date' => '2026-01-31'])->assertUnprocessable();
        $this->putJson('/api/shifts/'.$first, ['start_time' => '18:00'])->assertUnprocessable();
        $this->putJson('/api/shifts/'.$first, ['start_time' => '10:00'])->assertOk()->assertJsonPath('data.start_time', '10:00');
        $this->deleteJson('/api/shifts/'.$first)->assertNoContent();
        $this->deleteJson('/api/shifts/'.$second)->assertNoContent();
        $this->assertSame(0, $this->schedule->fresh()->progress);
        $this->assertNull($this->schedule->fresh()->period_end);
    }

    public function test_period_filter_and_schedule_isolation(): void
    {
        $this->schedule->assignedShifts()->create($this->shift());
        $this->schedule->assignedShifts()->create($this->shift(['date' => '2026-02-01']));
        $other = Schedule::create(['title' => 'Другой', 'status' => 'draft']);
        $other->assignedShifts()->create($this->shift());
        $url = '/api/schedules/'.$this->schedule->id.'/shifts';
        $this->getJson($url.'?from=2026-02-01&to=2026-02-28')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.date', '2026-02-01');
        $this->getJson($url.'?from=bad')->assertUnprocessable();
        $this->getJson($url.'?from=2026-03-01&to=2026-01-01')->assertUnprocessable();
        $this->postJson('/api/schedules/'.$this->schedule->id.'/save', ['shifts' => []])->assertOk();
        $this->assertSame(1, $other->assignedShifts()->count());
    }

    public function test_employee_avatars_and_templates_come_from_api(): void
    {
        $this->employee->update(['avatar_url' => '/images/employees/anna.png']);
        $this->getJson('/api/employees')->assertOk()->assertJsonPath('data.0.avatar_url', '/images/employees/anna.png');
        ShiftTemplate::create(['name' => 'Утро', 'start_time' => '07:00', 'end_time' => '15:00', 'color' => 'cornflower']);
        $this->getJson('/api/shift-templates')->assertOk()->assertJsonPath('data.0.start_time', '07:00');
        $this->postJson('/api/schedules', ['title' => 'Новый'])->assertCreated()->assertJsonPath('data.status', 'draft');
    }

    public function test_equivalent_employee_ids_cannot_bypass_duplicate_cell_validation(): void
    {
        $this->postJson('/api/schedules/'.$this->schedule->id.'/save', ['shifts' => [
            $this->shift(),
            $this->shift(['employee_id' => '+'.$this->employee->id]),
        ]])->assertUnprocessable();
        $this->assertDatabaseCount('assigned_shifts', 0);
    }

    public function test_occupancy_is_rechecked_after_request_validation(): void
    {
        $request = \App\Http\Requests\StoreAssignedShiftRequest::create(
            '/api/schedules/'.$this->schedule->id.'/shifts',
            'POST',
            $this->shift()
        );
        $route = new \Illuminate\Routing\Route('POST', 'api/schedules/{schedule}/shifts', fn () => null);
        $route->bind($request)->setParameter('schedule', $this->schedule);
        $request->setRouteResolver(fn () => $route);
        $request->setContainer($this->app)->setRedirector($this->app['redirect']);
        $request->validateResolved();
        // Another request commits after the first one has validated its empty cell.
        $competing = $this->schedule->assignedShifts()->create($this->shift(['start_time' => '08:00']));
        try {
            $this->app->make(\App\Http\Controllers\Api\AssignedShiftController::class)->store($request, $this->schedule);
            $this->fail('A competing assignment must produce 422.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertSame(422, $exception->status);
        }
        $this->assertDatabaseCount('assigned_shifts', 1);
        $this->assertDatabaseHas('assigned_shifts', ['id' => $competing->id, 'start_time' => '08:00']);
    }
}
