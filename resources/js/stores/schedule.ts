import { defineStore } from "pinia";
import { computed, ref, watch } from "vue";
import {
    fetchEmployees,
    fetchSchedule,
    fetchShifts,
    fetchTemplates,
    publishSchedule,
    saveDraft,
} from "@/api/schedules";
import { useSchedulesListStore } from "@/stores/schedulesList";
import { useToastStore } from "@/stores/toast";
import type {
    AssignedShift,
    AssignedShiftDto,
    CellSelection,
    Employee,
    Schedule,
    ShiftTemplate,
    ViewMode,
} from "@/types";
import {
    addDays,
    addMonths,
    createLocalId,
    hoursBetween,
    parseIsoDate,
    timeErrors,
    toIsoDate,
    weekDays,
} from "@/utils/dates";

export const useScheduleStore = defineStore("schedule", () => {
    const schedule = ref<Schedule | null>(null);

    const employees = ref<Employee[]>([]);
    const templates = ref<ShiftTemplate[]>([]);
    const shifts = ref<AssignedShift[]>([]);
    const viewMode = ref<ViewMode>("week");
    const anchorDate = ref(new Date());
    const searchQuery = ref("");
    const selectedCells = ref<CellSelection[]>([]);
    const selectedTemplateId = ref<number | null>(null);
    const editingShiftLocalId = ref<string | null>(null);
    const quickDate = ref<string | null>(null);
    const customStart = ref("09:00");
    const customEnd = ref("17:00");
    const customErrors = ref<{ start?: string; end?: string }>({});
    const editErrors = ref<{ start?: string; end?: string }>({});
    const dirty = ref(false);
    const editFormDirty = ref(false);
    const loading = ref(false);
    const saving = ref(false);
    const notFound = ref(false);
    const dragOverKey = ref<string | null>(null);
    let loadVersion = 0;
    const toast = useToastStore();

    const filteredEmployees = computed(() => {
        const words = searchQuery.value.trim().toLocaleLowerCase("ru").split(/\s+/).filter(Boolean);
        return employees.value.filter((e) =>
            words.every((word) =>
                (e.last_name + " " + e.first_name).toLocaleLowerCase("ru").includes(word),
            ),
        );
    });
    const visibleDays = computed(() =>
        viewMode.value === "week"
            ? weekDays(anchorDate.value)
            : Array.from(
                  {
                      length: new Date(
                          anchorDate.value.getFullYear(),
                          anchorDate.value.getMonth() + 1,
                          0,
                      ).getDate(),
                  },
                  (_, i) =>
                      new Date(anchorDate.value.getFullYear(), anchorDate.value.getMonth(), i + 1),
              ),
    );
    const visibleDates = computed(() => visibleDays.value.map(toIsoDate));
    const status = computed(() => schedule.value?.status ?? "draft");
    const editable = computed(() => !!schedule.value && !loading.value && !saving.value);
    const shiftsByCell = computed(
        () => new Map(shifts.value.map((s) => [cellKey(s.employee_id, s.date), s])),
    );
    const selectedKeys = computed(
        () => new Set(selectedCells.value.map((c) => cellKey(c.employeeId, c.date))),
    );
    const visibleHours = computed(() => {
        const result = new Map<number, number>();
        const dates = new Set(visibleDates.value);
        shifts.value.forEach((s) => {
            if (dates.has(s.date))
                result.set(
                    s.employee_id,
                    (result.get(s.employee_id) ?? 0) + hoursBetween(s.start_time, s.end_time),
                );
        });
        return result;
    });

    function cellKey(employeeId: number, date: string): string {
        return employeeId + ":" + date;
    }

    function getShift(employeeId: number, date: string) {
        return shiftsByCell.value.get(cellKey(employeeId, date));
    }

    function employeeHours(employeeId: number): number {
        return visibleHours.value.get(employeeId) ?? 0;
    }

    function coverageForDate(date: string): number {
        return shifts.value.filter((s) => s.date === date).length;
    }

    function isSelected(employeeId: number, date: string): boolean {
        return selectedKeys.value.has(cellKey(employeeId, date));
    }

    function clearSelection(): void {
        selectedCells.value = [];
    }

    function closeTransient(): void {
        clearSelection();
        selectedTemplateId.value = null;
        editingShiftLocalId.value = null;
        quickDate.value = null;
        dragOverKey.value = null;
        customErrors.value = {};
        editErrors.value = {};
        editFormDirty.value = false;
    }

    watch([viewMode, anchorDate, searchQuery], closeTransient, {
        flush: "sync",
    });
    watch(
        editingShiftLocalId,
        () => {
            editErrors.value = {};
            editFormDirty.value = false;
        },
        { flush: "sync" },
    );

    function markDirty(): void {
        dirty.value = true;
        toast.clear();
    }

    function toggleCell(employeeId: number, date: string): void {
        if (!editable.value) return;
        if (selectedTemplateId.value) {
            const template = templates.value.find((t) => t.id === selectedTemplateId.value);
            if (template) assignTemplateToCells(template, [{ employeeId, date }]);
            return;
        }
        const index = selectedCells.value.findIndex(
            (c) => c.employeeId === employeeId && c.date === date,
        );
        if (index >= 0) selectedCells.value.splice(index, 1);
        else selectedCells.value.push({ employeeId, date });
        quickDate.value = null;
    }

    function addShift(
        cell: CellSelection,
        start: string,
        end: string,
        templateId: number | null,
        color: string,
    ): boolean {
        if (
            !editable.value ||
            !schedule.value ||
            getShift(cell.employeeId, cell.date) ||
            !employees.value.some((e) => e.id === cell.employeeId)
        )
            return false;
        shifts.value.push({
            id: null,
            localId: createLocalId(),
            schedule_id: schedule.value.id,
            employee_id: cell.employeeId,
            template_id: templateId,
            date: cell.date,
            start_time: start,
            end_time: end,
            color,
        });
        markDirty();
        return true;
    }

    function assignTemplateToCells(template: ShiftTemplate, cells: CellSelection[]): number {
        if (!editable.value) return 0;
        let count = 0;
        cells.forEach((cell) => {
            if (addShift(cell, template.start_time, template.end_time, template.id, template.color))
                count++;
        });
        return count;
    }

    function assignDate(template: ShiftTemplate, date: string): void {
        assignTemplateToCells(
            template,
            filteredEmployees.value.map((e) => ({ employeeId: e.id, date })),
        );
        quickDate.value = null;
    }

    function chooseTemplate(template: ShiftTemplate): void {
        if (!editable.value) return;
        if (selectedCells.value.length) {
            assignTemplateToCells(template, [...selectedCells.value]);
            clearSelection();
        } else
            selectedTemplateId.value =
                selectedTemplateId.value === template.id ? null : template.id;
    }

    function assignCustomToCells(cells: CellSelection[]): boolean {
        if (!editable.value || !cells.length) return false;
        customErrors.value = timeErrors(customStart.value, customEnd.value);
        if (Object.keys(customErrors.value).length) return false;
        cells.forEach((cell) => addShift(cell, customStart.value, customEnd.value, null, "brand"));
        return true;
    }

    function updateShiftTimes(localId: string, start: string, end: string): boolean {
        if (!editable.value) return false;
        editErrors.value = timeErrors(start, end);
        if (Object.keys(editErrors.value).length) return false;
        const shift = shifts.value.find((s) => s.localId === localId);
        if (!shift) return false;
        shift.start_time = start;
        shift.end_time = end;
        shift.template_id = null;
        shift.color = "brand";
        markDirty();
        return true;
    }

    function removeShift(localId: string): void {
        if (!editable.value || !shifts.value.some((s) => s.localId === localId)) return;
        shifts.value = shifts.value.filter((s) => s.localId !== localId);
        if (editingShiftLocalId.value === localId) editingShiftLocalId.value = null;
        markDirty();
    }

    function clearSelectedShifts(): void {
        if (!editable.value) return;
        const before = shifts.value.length;
        shifts.value = shifts.value.filter(
            (s) => !selectedKeys.value.has(cellKey(s.employee_id, s.date)),
        );
        if (shifts.value.length !== before) markDirty();
        editingShiftLocalId.value = null;
    }

    function moveShift(localId: string, employeeId: number, date: string): boolean {
        if (
            !editable.value ||
            getShift(employeeId, date) ||
            !employees.value.some((e) => e.id === employeeId)
        )
            return false;
        const shift = shifts.value.find((s) => s.localId === localId);
        if (!shift) return false;
        shift.employee_id = employeeId;
        shift.date = date;
        dragOverKey.value = null;
        markDirty();
        return true;
    }

    function shiftPeriod(direction: -1 | 1): void {
        anchorDate.value =
            viewMode.value === "week"
                ? addDays(anchorDate.value, direction * 7)
                : addMonths(anchorDate.value, direction);
    }

    function hydrate(data: AssignedShiftDto[]): AssignedShift[] {
        return data.map((s) => ({
            ...s,
            localId: s.id ? "db-" + s.id : createLocalId(),
        }));
    }

    async function loadEditor(scheduleId: number): Promise<void> {
        const version = ++loadVersion;
        schedule.value = null;
        shifts.value = [];
        employees.value = [];
        templates.value = [];
        closeTransient();
        dirty.value = false;
        toast.clear();
        searchQuery.value = "";
        viewMode.value = "week";
        customStart.value = "09:00";
        customEnd.value = "17:00";
        notFound.value = false;
        loading.value = true;
        try {
            const [graph, people, patterns, assigned] = await Promise.all([
                fetchSchedule(scheduleId),
                fetchEmployees(),
                fetchTemplates(),
                fetchShifts(scheduleId),
            ]);
            if (version !== loadVersion) return;
            schedule.value = graph;
            employees.value = people;
            templates.value = patterns;
            shifts.value = hydrate(assigned);
            const firstDate = assigned.map((s) => s.date).sort()[0];
            anchorDate.value = firstDate ? parseIsoDate(firstDate) : new Date();
        } catch (e) {
            if (version !== loadVersion) return;
            notFound.value = (e as { response?: { status?: number } }).response?.status === 404;
        } finally {
            if (version === loadVersion) loading.value = false;
        }
    }

    async function persist(publish: boolean): Promise<void> {
        if (!editable.value || !schedule.value) return;
        saving.value = true;
        toast.clear();
        const version = loadVersion;
        const payload = shifts.value.map(({ localId: _localId, ...shift }) => ({
            ...shift,
        }));
        try {
            const result = await (publish ? publishSchedule : saveDraft)(
                schedule.value.id,
                payload,
            );
            if (version !== loadVersion) return;
            const { assigned_shifts, ...graph } = result;
            schedule.value = graph;
            shifts.value = hydrate(assigned_shifts);
            useSchedulesListStore().upsert(graph);
            dirty.value = false;
            closeTransient();
            toast.success(publish ? "График опубликован" : "Черновик сохранён");
        } catch (e) {
            if (version !== loadVersion) return;
            const details = (
                e as {
                    response?: { data?: { errors?: Record<string, string[]> } };
                }
            ).response?.data?.errors;
            toast.error(
                details
                    ? Object.values(details).flat()[0]
                    : publish
                      ? "Не удалось опубликовать график. Правки сохранены в редакторе."
                      : "Не удалось сохранить график. Правки сохранены в редакторе.",
            );
        } finally {
            saving.value = false;
        }
    }
    const save = () => persist(false);
    const publish = () => persist(true);
    return {
        schedule,
        employees,
        templates,
        shifts,
        viewMode,
        anchorDate,
        searchQuery,
        selectedCells,
        selectedTemplateId,
        editingShiftLocalId,
        quickDate,
        customStart,
        customEnd,
        customErrors,
        editErrors,
        dirty,
        editFormDirty,
        loading,
        saving,
        notFound,
        dragOverKey,
        filteredEmployees,
        visibleDays,
        visibleDates,
        status,
        editable,
        cellKey,
        getShift,
        employeeHours,
        coverageForDate,
        isSelected,
        clearSelection,
        closeTransient,
        toggleCell,
        assignTemplateToCells,
        assignDate,
        chooseTemplate,
        assignCustomToCells,
        updateShiftTimes,
        removeShift,
        clearSelectedShifts,
        moveShift,
        shiftPeriod,
        loadEditor,
        save,
        publish,
    };
});
