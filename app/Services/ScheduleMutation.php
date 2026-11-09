<?php

namespace App\Services;

use App\Models\AssignedShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleMutation
{
    /** Must be the first database operation inside every schedule write transaction. */
    public static function lock(int $scheduleId): void
    {
        // A no-op UPDATE obtains a write lock on SQLite and a row lock on MySQL.
        // Unlike SELECT FOR UPDATE, it also serializes SQLite writers before
        // checking occupancy. It leaves all existing data and timestamps intact.
        DB::table('schedules')->where('id', $scheduleId)->update(['updated_at' => DB::raw('updated_at')]);
    }

    public static function assertAvailable(int $scheduleId, int $employeeId, string $date, ?int $ignoreId = null): void
    {
        $query = AssignedShift::where('schedule_id', $scheduleId)->where('employee_id', $employeeId)->whereDate('date', $date);
        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }
        if ($query->exists()) {
            throw ValidationException::withMessages(['date' => 'На эту дату сотруднику уже назначена смена.']);
        }
    }
}
