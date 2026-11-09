<template>
    <ModalDialog
        v-if="shift"
        title="Редактирование смены"
        title-id="edit-shift-title"
        :busy="store.saving"
        @close="store.editingShiftLocalId = null"
    >
        <form class="modal__body" novalidate @submit.prevent="save">
            <AppText size="sm" tone="secondary">{{ employeeName }} · {{ shift.date }}</AppText>
            <div class="time-fields">
                <AppInput
                    id="edit-start"
                    v-model="start"
                    label="Начало"
                    type="time"
                    size="lg"
                    :disabled="store.saving"
                    :error="store.editErrors.start"
                />
                <AppInput
                    id="edit-end"
                    v-model="end"
                    label="Конец"
                    type="time"
                    size="lg"
                    :disabled="store.saving"
                    :error="store.editErrors.end"
                />
            </div>
            <div class="modal__actions">
                <AppButton
                    variant="danger"
                    :disabled="store.saving"
                    @click="store.removeShift(shift.localId)"
                >
                    Удалить смену
                </AppButton>
                <AppButton variant="primary" type="submit" :disabled="store.saving">
                    Применить
                </AppButton>
            </div>
        </form>
    </ModalDialog>
</template>

<script setup lang="ts">
    import { computed, ref, watch } from "vue";
    import AppButton from "@/components/ui/AppButton.vue";
    import AppInput from "@/components/ui/AppInput.vue";
    import AppText from "@/components/ui/AppText.vue";
    import ModalDialog from "@/components/ui/ModalDialog.vue";
    import { useScheduleStore } from "@/stores/schedule";

    const store = useScheduleStore();
    const shift = computed(() => store.shifts.find((s) => s.localId === store.editingShiftLocalId));
    const employeeName = computed(
        () => store.employees.find((e) => e.id === shift.value?.employee_id)?.full_name,
    );
    const start = ref("");
    const end = ref("");
    watch(
        shift,
        (value) => {
            if (value) {
                start.value = value.start_time;
                end.value = value.end_time;
            }
        },
        { immediate: true },
    );
    watch(
        [start, end, shift],
        () => {
            store.editFormDirty =
                !!shift.value &&
                (start.value !== shift.value.start_time || end.value !== shift.value.end_time);
        },
        { flush: "sync" },
    );
    function save() {
        if (shift.value && store.updateShiftTimes(shift.value.localId, start.value, end.value))
            store.editingShiftLocalId = null;
    }
</script>
