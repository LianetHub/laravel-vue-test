<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Schedule;

class ScheduleSummary
{
    public static function refresh(Schedule $schedule): void
    {
        $employees = Employee::count();
        $assigned = $schedule->assignedShifts()->distinct()->count('employee_id');
        $schedule->update([
            'employees_count' => $employees,
            'progress' => $employees ? min(100, (int) round($assigned / $employees * 100)) : 0,
            'period_end' => $schedule->assignedShifts()->max('date'),
        ]);
    }
}
