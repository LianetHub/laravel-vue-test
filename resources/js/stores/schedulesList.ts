import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { fetchSchedules } from "@/api/schedules";
import type { Schedule, ScheduleStatus } from "@/types";

export const useSchedulesListStore = defineStore("schedulesList", () => {
    const schedules = ref<Schedule[]>([]);

    const loading = ref(false);
    const error = ref<string | null>(null);
    const loaded = ref(false);

    const published = computed(() => schedules.value.filter((s) => s.status === "published"));
    const drafts = computed(() => schedules.value.filter((s) => s.status === "draft"));

    async function load(force = false): Promise<void> {
        if (loading.value) return;
        if (loaded.value && !force) return;

        loading.value = true;
        error.value = null;

        try {
            schedules.value = await fetchSchedules();
            loaded.value = true;
        } catch {
            error.value = "Не удалось загрузить список графиков";
            throw new Error(error.value);
        } finally {
            loading.value = false;
        }
    }

    async function refresh(): Promise<void> {
        await load(true);
    }

    function upsert(schedule: Schedule): void {
        const index = schedules.value.findIndex((s) => s.id === schedule.id);
        if (index >= 0) {
            schedules.value.splice(index, 1, schedule);
        } else {
            schedules.value.unshift(schedule);
        }
        schedules.value = [...schedules.value].sort((a, b) => {
            const aTime = a.updated_at ? Date.parse(a.updated_at) : 0;
            const bTime = b.updated_at ? Date.parse(b.updated_at) : 0;
            return bTime - aTime;
        });
    }

    function byStatus(status: ScheduleStatus | "all"): Schedule[] {
        if (status === "all") return schedules.value;
        return schedules.value.filter((s) => s.status === status);
    }

    return {
        schedules,
        loading,
        error,
        loaded,
        published,
        drafts,
        load,
        refresh,
        upsert,
        byStatus,
    };
});
