<template>
    <div
        class="grid-scroll"
        tabindex="0"
        :aria-label="mode === 'month' ? 'График на месяц' : 'График на неделю'"
        @scroll="closeMenu"
    >
        <div
            class="calendar-table"
            :class="'calendar-table--' + mode"
            role="table"
            aria-label="Смены сотрудников"
            :style="{
                '--day-count': store.visibleDays.length,
            }"
        >
            <div class="grid-header calendar-row" role="row">
                <div class="grid-header__employee" role="columnheader">Сотрудник</div>
                <div
                    v-for="day in store.visibleDays"
                    :key="toIsoDate(day)"
                    class="date-heading"
                    role="columnheader"
                >
                    <button
                        type="button"
                        class="date-button"
                        :disabled="!store.editable"
                        :aria-expanded="store.quickDate === toIsoDate(day)"
                        :aria-label="'Назначить смены на ' + toIsoDate(day)"
                        @click.stop="toggleMenu($event, toIsoDate(day))"
                    >
                        <span>
                            {{
                                day.toLocaleDateString("ru-RU", {
                                    weekday: "short",
                                })
                            }}
                        </span>
                        <span>{{ day.getDate() }}</span>
                    </button>
                    <Teleport to="body">
                        <div
                            v-if="store.quickDate === toIsoDate(day)"
                            class="quick-menu date-quick-menu"
                            :style="menuStyle"
                            role="menu"
                            @keydown.esc="closeMenu"
                        >
                            <p>Назначить в свободные ячейки</p>
                            <button
                                v-for="template in store.templates"
                                :key="template.id"
                                type="button"
                                role="menuitem"
                                @click="store.assignDate(template, toIsoDate(day))"
                            >
                                <span>{{ template.name }}</span>
                                <small>{{ template.start_time }}–{{ template.end_time }}</small>
                            </button>
                            <p v-if="!store.templates.length">Нет доступных шаблонов</p>
                        </div>
                    </Teleport>
                </div>
            </div>
            <div
                v-for="employee in employees"
                :key="employee.id"
                class="grid-row calendar-row"
                role="row"
            >
                <AgentCell :employee="employee" :hours="store.employeeHours(employee.id)" />
                <ShiftCell
                    v-for="date in store.visibleDates"
                    :key="date"
                    :employee-id="employee.id"
                    :employee-name="employee.full_name"
                    :date="date"
                    :shift="store.getShift(employee.id, date)"
                    :selected="store.isSelected(employee.id, date)"
                    :compact="mode === 'month'"
                    :drag-over="store.dragOverKey === store.cellKey(employee.id, date)"
                    :disabled="!store.editable"
                    @select="store.toggleCell(employee.id, date)"
                    @edit="store.editingShiftLocalId = $event"
                    @drop-template="assign(employee.id, date, $event)"
                    @move-shift="store.moveShift($event, employee.id, date)"
                    @drag-over="store.dragOverKey = store.cellKey(employee.id, date)"
                    @drag-leave="store.dragOverKey = null"
                />
            </div>
            <div class="coverage-row calendar-row" role="row">
                <div class="coverage-row__label">Покрытие</div>
                <div
                    v-for="date in store.visibleDates"
                    :key="date"
                    class="coverage-row__cell"
                    :data-coverage-date="date"
                    :class="{ 'has-value': store.coverageForDate(date) > 0 }"
                >
                    {{ store.coverageForDate(date) }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, onMounted, onBeforeUnmount } from "vue";
    import { useScheduleStore } from "@/stores/schedule";
    import type { Employee, ViewMode } from "@/types";
    import { toIsoDate } from "@/utils/dates";
    import AgentCell from "./AgentCell.vue";
    import ShiftCell from "./ShiftCell.vue";
    defineProps<{ employees: Employee[]; mode: ViewMode }>();

    const store = useScheduleStore();
    const menuStyle = ref({ left: "0px", top: "0px", maxHeight: "360px" });

    function closeMenu() {
        store.quickDate = null;
    }

    function toggleMenu(event: MouseEvent, date: string) {
        if (store.quickDate === date) {
            closeMenu();
            return;
        }
        const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();
        const height = Math.min(360, 62 + Math.max(1, store.templates.length) * 42);
        const top =
            rect.bottom + height <= window.innerHeight - 8
                ? rect.bottom
                : Math.max(8, rect.top - height);
        menuStyle.value = {
            left: Math.max(8, Math.min(rect.left, window.innerWidth - 258)) + "px",
            top: top + "px",
            maxHeight: window.innerHeight - top - 8 + "px",
        };
        store.quickDate = date;
    }

    onMounted(() => {
        window.addEventListener("resize", closeMenu);
        window.addEventListener("scroll", closeMenu);
    });
    onBeforeUnmount(() => {
        window.removeEventListener("resize", closeMenu);
        window.removeEventListener("scroll", closeMenu);
    });
    function assign(employeeId: number, date: string, templateId: number) {
        const template = store.templates.find((t) => t.id === templateId);
        if (template) store.assignTemplateToCells(template, [{ employeeId, date }]);
    }
</script>

<style lang="scss" scoped>
    .grid-scroll {
        flex: 1;
        min-height: 0;
        min-width: 0;
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: var(--scrollbar) transparent;
    }

    .calendar-table {
        min-width: 990px;
        min-height: 100%;
        display: flex;
        flex-direction: column;
        isolation: isolate;

        .calendar-row {
            display: grid;
            grid-template-columns: 220px repeat(var(--day-count), minmax(0, 1fr));
            flex-shrink: 0;
        }

        .grid-header {
            height: 48px;
            position: sticky;
            top: 0;
            z-index: 6;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border);

            &__employee {
                position: sticky;
                left: 0;
                z-index: 7;
                padding: 12px;
                background: var(--bg-surface);
                color: var(--text-secondary);
            }
        }

        .date-heading {
            position: relative;
            min-width: 0;
        }

        .date-button {
            display: flex;
            width: 100%;
            height: 100%;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 4px;
            border: 0;
            background: transparent;
            color: var(--text-secondary);
            font-weight: 400;

            &:hover {
                color: var(--brand-strong);
                background: var(--bg-muted);
            }
        }

        .grid-row {
            height: 72px;
            background: var(--bg-surface);

            &:nth-child(odd) {
                background: var(--bg-card);
            }
        }

        .coverage-row {
            position: sticky;
            bottom: 0;
            z-index: 5;
            height: 48px;
            background: var(--bg-surface);
            margin-top: auto;

            &__label {
                position: sticky;
                left: 0;
                z-index: 6;
                padding: 12px;
                color: var(--text-secondary);
                background: var(--bg-surface);
            }

            &__cell {
                display: flex;
                align-items: center;
                justify-content: center;
                color: var(--danger-cell);
                background: var(--danger-cell-bg);
                font-size: 16px;

                &.has-value {
                    color: var(--brand-strong);
                    background: var(--brand-soft);
                }
            }
        }

        &--month {
            width: 100%;
            min-width: calc(220px + (var(--day-count) * 64px));

            .calendar-row {
                grid-template-columns: 220px repeat(var(--day-count), minmax(64px, 1fr));
            }

            .grid-header {
                height: 54px;

                &__employee {
                    padding-block: 15px;
                }
            }

            .date-button {
                flex-direction: column;
                gap: 0;
                font-size: 14px;
                line-height: 18px;

                span:last-child {
                    color: var(--text-primary);
                }
            }

            :deep(.shift-chip__hours) {
                font-size: 16px;
                line-height: 24px;
            }
        }

        &:not(.calendar-table--month) .grid-row {
            grid-template-columns: 220px repeat(7, minmax(110px, 1fr));
            padding-right: 3px;
        }
    }
</style>
