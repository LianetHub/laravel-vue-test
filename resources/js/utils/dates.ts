const DAY_LABELS = ["вс", "пн", "вт", "ср", "чт", "пт", "сб"];

export function toIsoDate(date: Date): string {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, "0");
    const d = String(date.getDate()).padStart(2, "0");
    return `${y}-${m}-${d}`;
}

export function parseIsoDate(value: string): Date {
    const [y, m, d] = value.split("-").map(Number);
    return new Date(y, m - 1, d);
}

export function startOfWeek(date: Date): Date {
    const result = new Date(date);
    const day = result.getDay();
    const diff = day === 0 ? -6 : 1 - day;
    result.setDate(result.getDate() + diff);
    result.setHours(0, 0, 0, 0);
    return result;
}

export function addDays(date: Date, days: number): Date {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

export function addMonths(date: Date, months: number): Date {
    const result = new Date(date);
    const day = result.getDate();
    result.setDate(1);
    result.setMonth(result.getMonth() + months);
    result.setDate(
        Math.min(day, new Date(result.getFullYear(), result.getMonth() + 1, 0).getDate()),
    );
    return result;
}

export function weekDays(anchor: Date): Date[] {
    const start = startOfWeek(anchor);
    return Array.from({ length: 7 }, (_, i) => addDays(start, i));
}

export function monthGridDays(anchor: Date): Date[] {
    const first = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
    const start = startOfWeek(first);
    const last = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0);
    const endWeekStart = startOfWeek(last);
    const end = addDays(endWeekStart, 6);
    const days: Date[] = [];
    let cursor = start;
    while (cursor <= end) {
        days.push(new Date(cursor));
        cursor = addDays(cursor, 1);
    }
    return days;
}

export function formatDayHeader(date: Date): string {
    const label = DAY_LABELS[date.getDay()];
    return `${label} ${date.getDate()}`;
}

export function formatMonthTitle(date: Date): string {
    return date.toLocaleDateString("ru-RU", { month: "long", year: "numeric" });
}

export function formatWeekRange(anchor: Date): string {
    const days = weekDays(anchor);
    const start = days[0];
    const end = days[6];
    const startLabel = start.toLocaleDateString("ru-RU", {
        day: "numeric",
        month: "short",
    });
    const endLabel = end.toLocaleDateString("ru-RU", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
    return `${startLabel} — ${endLabel}`;
}

export function hoursBetween(start: string, end: string): number {
    if (!isTime(start) || !isTime(end)) return 0;
    const [sh, sm] = start.split(":").map(Number);
    const [eh, em] = end.split(":").map(Number);
    return Math.max(0, eh + em / 60 - (sh + sm / 60));
}

export function createLocalId(): string {
    return `local-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

export function isTime(value: string): boolean {
    return /^([01]\d|2[0-3]):[0-5]\d$/.test(value);
}

export function timeErrors(start: string, end: string): { start?: string; end?: string } {
    const errors: { start?: string; end?: string } = {};
    if (!isTime(start)) errors.start = "Укажите время начала.";
    if (!isTime(end)) errors.end = "Укажите время окончания.";
    if (!errors.start && !errors.end && start >= end)
        errors.end = "Окончание должно быть позже начала.";
    return errors;
}

export function formatHours(hours: number): string {
    return new Intl.NumberFormat("ru-RU", { maximumFractionDigits: 2 }).format(hours);
}
