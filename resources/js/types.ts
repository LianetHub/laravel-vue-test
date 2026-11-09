export type ScheduleStatus = "draft" | "published";
export type ViewMode = "week" | "month";
export type ShiftColor = "cornflower" | "mint" | "lavanda" | "brand";

export interface Employee {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    position: string;
    avatar_url?: string | null;
}

export interface ShiftTemplate {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
    color: ShiftColor | string;
}

export interface Schedule {
    id: number;
    title: string;
    address: string | null;
    status: ScheduleStatus;
    employees_count: number;
    progress: number;
    period_end: string | null;
    updated_at: string | null;
}

export interface AssignedShiftDto {
    id: number | null;
    schedule_id: number;
    employee_id: number;
    template_id: number | null;
    date: string;
    start_time: string;
    end_time: string;
    color: string | null;
}

export interface AssignedShift extends AssignedShiftDto {
    localId: string;
}
export interface ScheduleSyncResult extends Schedule {
    assigned_shifts: AssignedShiftDto[];
}

export interface CellSelection {
    employeeId: number;
    date: string;
}
