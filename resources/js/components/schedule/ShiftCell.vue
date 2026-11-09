<template>
    <div
        class="shift-slot"
        :class="{ 'is-selected': selected, 'is-dragover': dragOver }"
        :data-employee-id="employeeId"
        :data-date="date"
        @dragover="over"
        @dragleave="leave"
        @drop="drop"
    >
        <button
            type="button"
            class="shift-cell"
            :class="{ 'has-shift': shift }"
            :aria-label="label"
            :aria-pressed="selected"
            :disabled="disabled"
            :draggable="!!shift && !disabled"
            @dragstart="startDrag"
            @dragend="emit('dragLeave')"
            @click="click"
            @keydown.space.prevent="emit('select')"
        >
            <ShiftChip
                v-if="shift"
                :start="shift.start_time"
                :end="shift.end_time"
                :color="shift.color"
                :compact="compact"
            />
            <span v-else class="empty-cell">
                <IconCellAdd aria-hidden="true" />
            </span>
        </button>
    </div>
</template>

<script setup lang="ts">
    import { computed } from "vue";
    import type { AssignedShift } from "@/types";
    import IconCellAdd from "@/icons/cell-add.svg";
    import ShiftChip from "./ShiftChip.vue";

    const props = defineProps<{
        employeeId: number;
        date: string;
        employeeName?: string;
        shift?: AssignedShift;
        selected: boolean;
        dragOver: boolean;
        disabled?: boolean;
        compact?: boolean;
    }>();
    const emit = defineEmits<{
        select: [];
        edit: [localId: string];
        dropTemplate: [templateId: number];
        moveShift: [localId: string];
        dragOver: [];
        dragLeave: [];
    }>();
    const label = computed(
        () =>
            (props.employeeName || "Сотрудник " + props.employeeId) +
            ", " +
            props.date +
            (props.shift
                ? ", " +
                  props.shift.start_time +
                  "–" +
                  props.shift.end_time +
                  ". Редактировать смену"
                : ", пустая ячейка"),
    );
    function click(event: MouseEvent) {
        if (props.shift && !event.ctrlKey && !event.metaKey && !event.shiftKey)
            emit("edit", props.shift.localId);
        else emit("select");
    }

    function accepts(event: DragEvent): boolean {
        return (
            !props.disabled &&
            !props.shift &&
            !!event.dataTransfer &&
            Array.from(event.dataTransfer.types).some((t) =>
                ["application/x-shift-template", "application/x-assigned-shift"].includes(t),
            )
        );
    }

    function over(event: DragEvent) {
        if (!accepts(event)) return;
        event.preventDefault();
        event.dataTransfer!.dropEffect = event.dataTransfer!.types.includes(
            "application/x-assigned-shift",
        )
            ? "move"
            : "copy";
        emit("dragOver");
    }

    function leave(event: DragEvent) {
        if (
            event.relatedTarget instanceof Node &&
            (event.currentTarget as HTMLElement).contains(event.relatedTarget)
        )
            return;
        emit("dragLeave");
    }

    function drop(event: DragEvent) {
        emit("dragLeave");
        if (!accepts(event)) return;
        event.preventDefault();
        const localId = event.dataTransfer!.getData("application/x-assigned-shift");
        if (localId) {
            emit("moveShift", localId);
            return;
        }
        const id = Number(event.dataTransfer!.getData("application/x-shift-template"));
        if (Number.isInteger(id) && id > 0) emit("dropTemplate", id);
    }

    function startDrag(event: DragEvent) {
        if (!props.shift || props.disabled || !event.dataTransfer) {
            event.preventDefault();
            return;
        }
        event.dataTransfer.setData("application/x-assigned-shift", props.shift.localId);
        event.dataTransfer.effectAllowed = "move";
    }
</script>

<style lang="scss" scoped>
    .shift-slot {
        height: 72px;
        padding: 4px;
        min-width: 0;

        &.is-selected,
        &.is-dragover {
            background: var(--brand-selected);
            box-shadow: inset 0 0 0 1px var(--brand);

            .empty-cell {
                border: 0;

                svg {
                    visibility: visible;
                }
            }
        }
    }

    .shift-cell {
        width: 100%;
        height: 100%;
        padding: 0;
        border: 0;
        border-radius: 8px;
        background: transparent;
        display: flex;
        align-items: stretch;
        justify-content: center;

        &:hover:not(:disabled) .empty-cell {
            background: var(--brand-selected);
            border-color: var(--brand);

            svg {
                visibility: visible;
            }
        }

        &.has-shift {
            cursor: grab;

            &:active {
                cursor: grabbing;
            }
        }
    }

    .empty-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        border: 1px dashed var(--border);
        border-radius: 8px;
        color: var(--brand);

        svg {
            visibility: hidden;
        }
    }
</style>
