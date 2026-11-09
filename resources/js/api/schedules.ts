import client from "./client";
import type {
    AssignedShiftDto,
    Employee,
    Schedule,
    ScheduleSyncResult,
    ShiftTemplate,
} from "@/types";

function unwrap<T>(payload: { data: T } | T): T {
    if (payload && typeof payload === "object" && "data" in payload) {
        return (payload as { data: T }).data;
    }
    return payload as T;
}

export async function fetchEmployees(): Promise<Employee[]> {
    const { data } = await client.get("/employees");
    return unwrap(data);
}

export async function fetchTemplates(): Promise<ShiftTemplate[]> {
    const { data } = await client.get("/shift-templates");
    return unwrap(data);
}

export async function fetchSchedules(): Promise<Schedule[]> {
    const { data } = await client.get("/schedules");
    return unwrap(data);
}

export async function createSchedule(payload: {
    title: string;
    address?: string | null;
}): Promise<Schedule> {
    const { data } = await client.post("/schedules", payload);
    return unwrap(data);
}

export async function fetchSchedule(id: number): Promise<Schedule> {
    const { data } = await client.get(`/schedules/${id}`);
    return unwrap(data);
}

export async function fetchShifts(
    scheduleId: number,
    from?: string,
    to?: string,
): Promise<AssignedShiftDto[]> {
    const { data } = await client.get(`/schedules/${scheduleId}/shifts`, {
        params: { from, to },
    });
    return unwrap(data);
}

export async function saveSchedule(
    id: number,
    payload: { status: "draft" | "published"; shifts: AssignedShiftDto[] },
): Promise<ScheduleSyncResult> {
    const { data } = await client.put(`/schedules/${id}`, payload);
    return unwrap(data);
}

export async function saveDraft(
    id: number,
    shifts: AssignedShiftDto[],
): Promise<ScheduleSyncResult> {
    const { data } = await client.post(`/schedules/${id}/save`, {
        status: "draft",
        shifts,
    });
    return unwrap(data);
}

export async function publishSchedule(
    id: number,
    shifts: AssignedShiftDto[],
): Promise<ScheduleSyncResult> {
    const { data } = await client.post(`/schedules/${id}/publish`, {
        status: "published",
        shifts,
    });
    return unwrap(data);
}
