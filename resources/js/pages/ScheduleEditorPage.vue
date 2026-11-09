<template>
    <div class="app-shell">
        <AppSidebar />
        <main class="main main--editor">
            <RouterLink class="editor-back" :to="{ name: 'schedules' }">
                <span>
                    <IconChevronLeft aria-hidden="true" />
                </span>
                Графики смен
            </RouterLink>
            <ScheduleEditorSkeleton v-if="store.loading" />
            <div v-else-if="!store.schedule" class="empty-state empty-state--panel" role="alert">
                <AppTitle as="h1" size="lg">
                    {{ store.notFound ? "График не найден" : "Не удалось загрузить график" }}
                </AppTitle>
                <AppText size="md" tone="secondary">
                    {{
                        store.notFound
                            ? "Возможно, этот график был удалён."
                            : "Проверьте соединение и попробуйте ещё раз."
                    }}
                </AppText>
                <AppButton v-if="!store.notFound" variant="primary" @click="load">
                    Повторить
                </AppButton>
                <AppButton v-else variant="secondary" :to="{ name: 'schedules' }">
                    К списку графиков
                </AppButton>
            </div>
            <template v-else>
                <header class="editor-header">
                    <div class="editor-header__left">
                        <AppTitle as="h1" size="xl">График</AppTitle>
                        <AppText
                            class="editor-status"
                            as="span"
                            size="xs"
                            tone="secondary"
                            role="status"
                            :title="store.schedule.title"
                        >
                            {{
                                store.dirty
                                    ? "Есть несохранённые изменения"
                                    : store.status === "published"
                                      ? "Опубликован"
                                      : "Черновик"
                            }}
                        </AppText>
                    </div>
                    <div class="editor-header__actions">
                        <AppButton
                            variant="secondary"
                            :disabled="store.saving"
                            @click="store.publish()"
                        >
                            Опубликовать
                        </AppButton>
                        <AppButton variant="primary" :disabled="store.saving" @click="store.save()">
                            {{ store.saving ? "Сохранение…" : "Сохранить" }}
                        </AppButton>
                    </div>
                </header>
                <div class="editor-layout" :aria-busy="store.saving">
                    <section class="editor-board" aria-label="Редактор расписания">
                        <SelectionBar v-if="store.selectedCells.length" />
                        <div v-else class="editor-toolbar">
                            <AppInput
                                v-model="store.searchQuery"
                                type="search"
                                tone="muted"
                                size="lg"
                                placeholder="Поиск по ФИО"
                            >
                                <template #icon>
                                    <IconSearch />
                                </template>
                            </AppInput>
                            <SegmentedControl
                                v-model="store.viewMode"
                                class="view-mode-control"
                                aria-label="Режим просмотра"
                                :options="viewModeOptions"
                            />
                            <div class="period-nav">
                                <button
                                    class="icon-button"
                                    type="button"
                                    aria-label="Предыдущий период"
                                    @click="store.shiftPeriod(-1)"
                                >
                                    <IconChevronLeft aria-hidden="true" />
                                </button>
                                <span class="period-nav__label">{{ periodLabel }}</span>
                                <button
                                    class="icon-button"
                                    type="button"
                                    aria-label="Следующий период"
                                    @click="store.shiftPeriod(1)"
                                >
                                    <IconChevronLeft class="rotate-180" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                        <div v-if="!store.filteredEmployees.length" class="empty-state">
                            {{
                                store.employees.length
                                    ? "Сотрудники не найдены. Измените поисковый запрос."
                                    : "В графике пока нет сотрудников."
                            }}
                        </div>
                        <WeekGrid
                            v-else-if="store.viewMode === 'week'"
                            :employees="store.filteredEmployees"
                        />
                        <MonthGrid v-else :employees="store.filteredEmployees" />
                    </section>
                    <TemplatesPanel />
                </div>
            </template>
            <ShiftEditDialog />
        </main>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted, onBeforeUnmount, watch } from "vue";
    import { RouterLink, onBeforeRouteLeave, onBeforeRouteUpdate } from "vue-router";
    import AppSidebar from "@/components/layout/AppSidebar.vue";
    import MonthGrid from "@/components/schedule/MonthGrid.vue";
    import ScheduleEditorSkeleton from "@/components/schedule/ScheduleEditorSkeleton.vue";
    import SelectionBar from "@/components/schedule/SelectionBar.vue";
    import TemplatesPanel from "@/components/schedule/TemplatesPanel.vue";
    import WeekGrid from "@/components/schedule/WeekGrid.vue";
    import ShiftEditDialog from "@/components/schedule/ShiftEditDialog.vue";
    import AppButton from "@/components/ui/AppButton.vue";
    import AppInput from "@/components/ui/AppInput.vue";
    import AppText from "@/components/ui/AppText.vue";
    import AppTitle from "@/components/ui/AppTitle.vue";
    import SegmentedControl from "@/components/ui/SegmentedControl.vue";
    import IconChevronLeft from "@/icons/chevron-left.svg";
    import IconSearch from "@/icons/search.svg";
    import { useScheduleStore } from "@/stores/schedule";
    import type { ViewMode } from "@/types";
    import { formatMonthTitle, weekDays } from "@/utils/dates";

    const props = defineProps<{ id: string }>();
    const store = useScheduleStore();
    const viewModeOptions: { value: ViewMode; label: string }[] = [
        { value: "month", label: "Месяц" },
        { value: "week", label: "Неделя" },
    ];
    const periodLabel = computed(() => {
        if (store.viewMode === "month")
            return formatMonthTitle(store.anchorDate).replace(" г.", "");
        const days = weekDays(store.anchorDate);
        const first = days[0].toLocaleDateString("ru-RU", {
            day: "numeric",
            month: "short",
        });
        const last = days[6].toLocaleDateString("ru-RU", {
            day: "numeric",
            month: "short",
        });
        return first + " – " + last;
    });
    async function load() {
        await store.loadEditor(Number(props.id));
    }

    function canLeave(): boolean {
        if (store.saving) return false;
        return (
            !(store.dirty || store.editFormDirty) ||
            window.confirm("Есть несохранённые изменения. Покинуть график без сохранения?")
        );
    }
    onBeforeRouteLeave(canLeave);
    onBeforeRouteUpdate(canLeave);
    function unload(event: BeforeUnloadEvent) {
        if (store.dirty || store.editFormDirty || store.saving) {
            event.preventDefault();
            event.returnValue = "";
        }
    }

    function outside(event: MouseEvent) {
        if (!(event.target as Element).closest(".date-heading, .date-quick-menu"))
            store.quickDate = null;
    }

    function escape(event: KeyboardEvent) {
        if (event.key === "Escape") {
            store.quickDate = null;
            store.selectedTemplateId = null;
        }
    }

    onMounted(() => {
        void load();
        window.addEventListener("beforeunload", unload);
        document.addEventListener("click", outside);
        document.addEventListener("keydown", escape);
    });
    onBeforeUnmount(() => {
        window.removeEventListener("beforeunload", unload);
        document.removeEventListener("click", outside);
        document.removeEventListener("keydown", escape);
        store.closeTransient();
    });
    watch(() => props.id, load);
</script>

<style lang="scss" scoped>
    .main--editor {
        display: flex;
        flex-direction: column;
        height: 100vh;
        overflow: hidden;
        padding: 24px 30px;

        @include mq-md {
            height: auto;
            overflow: visible;
            padding: 20px 16px;
        }
    }

    .rotate-180 {
        transform: rotate(180deg);
    }

    .editor-back {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        height: 40px;
        margin-bottom: 24px;
        font-size: 16px;
        line-height: 22.4px;
        color: var(--text-primary);
        flex-shrink: 0;

        > span {
            display: inline-flex;
            padding: 8px;
            border-radius: 99px;
            background: var(--bg-surface);
            color: var(--text-primary);
        }

        &:hover > span {
            background: var(--bg-hover);
            color: var(--brand);
        }

        @include mq-sm {
            margin-bottom: 16px;
        }
    }

    .editor-header {
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
        flex-shrink: 0;

        &__left {
            display: flex;
            align-items: baseline;
            gap: 16px;
            min-width: 0;
        }

        &__actions {
            display: flex;
            gap: 4px;

            @include mq-sm {
                width: 100%;

                :deep(.app-button) {
                    flex: 1;
                }
            }
        }

        @include mq-sm {
            height: auto;
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }
    }

    :deep(.editor-status) {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        @include mq-md {
            font-size: 11px;
            max-width: 160px;
        }
    }

    .editor-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 335px;
        gap: 8px;
        align-items: stretch;
        flex: 1;
        min-height: 0;

        @include mq-lg {
            grid-template-columns: minmax(0, 1fr);
            flex: none;
            min-height: auto;
        }
    }

    .editor-board {
        display: flex;
        flex-direction: column;
        gap: 20px;
        background: var(--bg-surface);
        border-radius: 20px;
        padding: 12px;
        min-width: 0;
        min-height: 0;
        height: 100%;

        &:has(:deep(.selection-bar)) {
            gap: 16px;
        }

        @include mq-lg {
            height: auto;
            min-height: min(680px, calc(100vh - 220px));
        }

        @include mq-sm {
            gap: 12px;
            min-height: min(640px, calc(100vh - 200px));
        }
    }

    .editor-toolbar {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-shrink: 0;
        min-height: 48px;

        @include mq-xl-only {
            flex-wrap: wrap;

            :deep(.app-input-field--inline) {
                flex: 1;
            }

            :deep(.view-mode-control) {
                flex-basis: 240px;
            }
        }

        @include mq-lg {
            flex-wrap: wrap;

            :deep(.app-input-field--inline) {
                flex: 1;
            }

            :deep(.view-mode-control) {
                flex-basis: 240px;
            }
        }

        @include mq-md {
            :deep(.view-mode-control) {
                flex-basis: 220px;
            }
        }

        @include mq-sm {
            :deep(.app-input-field--inline) {
                flex-basis: 100%;
            }

            :deep(.view-mode-control) {
                flex: 1 1 100%;
            }
        }
    }

    .period-nav {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex: 1;
        min-width: 0;

        :deep(.icon-button) {
            padding: 4px;
        }

        &__label {
            flex: 1;
            min-width: 0;
            text-align: center;
            font-size: 12px;
            line-height: 16px;
            color: var(--text-secondary);
        }

        @include mq-xl-only {
            flex-basis: 100%;
            justify-content: flex-end;

            &__label {
                flex: 0 1 180px;
            }
        }

        @include mq-lg {
            flex-basis: 100%;
            justify-content: flex-end;

            &__label {
                flex: 0 1 180px;
            }
        }
    }
</style>
