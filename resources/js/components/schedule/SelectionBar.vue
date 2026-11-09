<template>
    <div class="selection-bar">
        <div class="selection-bar__summary">
            <span>Выделено:</span>
            <span class="selection-count">{{ store.selectedCells.length }} ячеек</span>
            <span>{{ employeeCount }} сотрудника(-ов) · {{ dateCount }} дн.</span>
        </div>
        <div class="selection-bar__actions">
            <div class="selection-menu-anchor">
                <button
                    type="button"
                    :disabled="!store.editable"
                    :aria-expanded="menu"
                    @click="menu = !menu"
                >
                    <IconTemplateAdd aria-hidden="true" />
                    Применить шаблон
                </button>
                <div v-if="menu" class="quick-menu" role="menu" @keydown.esc="menu = false">
                    <button
                        v-for="template in store.templates"
                        :key="template.id"
                        type="button"
                        role="menuitem"
                        @click="apply(template.id)"
                    >
                        <span>{{ template.name }}</span>
                        <small>{{ template.start_time }}–{{ template.end_time }}</small>
                    </button>
                    <p v-if="!store.templates.length">Нет доступных шаблонов</p>
                </div>
            </div>
            <button type="button" :disabled="!store.editable" @click="store.clearSelectedShifts()">
                <IconTrash aria-hidden="true" />
                Очистить
            </button>
            <button type="button" :disabled="!store.editable" @click="store.clearSelection()">
                <IconDeselect aria-hidden="true" />
                Снять
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { computed, ref, onMounted, onBeforeUnmount } from "vue";
    import IconDeselect from "@/icons/deselect.svg";
    import IconTemplateAdd from "@/icons/template-add.svg";
    import IconTrash from "@/icons/trash.svg";
    import { useScheduleStore } from "@/stores/schedule";

    const store = useScheduleStore();
    const menu = ref(false);
    const employeeCount = computed(
        () => new Set(store.selectedCells.map((c) => c.employeeId)).size,
    );
    const dateCount = computed(() => new Set(store.selectedCells.map((c) => c.date)).size);

    function apply(id: number) {
        const template = store.templates.find((t) => t.id === id);
        if (template) store.assignTemplateToCells(template, [...store.selectedCells]);
        store.clearSelection();
        menu.value = false;
    }

    function outside(event: MouseEvent) {
        if (!(event.target as Element).closest(".selection-menu-anchor")) menu.value = false;
    }

    onMounted(() => document.addEventListener("click", outside));
    onBeforeUnmount(() => document.removeEventListener("click", outside));
</script>

<style lang="scss" scoped>
    .selection-bar {
        min-height: 52px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: var(--chrome);
        color: var(--on-dark);
        border-radius: 8px;
        padding: 8px 12px;
        position: relative;
        z-index: 10;
        font-size: 14px;
        line-height: 20px;

        &__summary {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        &__actions {
            display: flex;
            align-items: center;
            gap: 8px;

            > button,
            :deep(.selection-menu-anchor > button) {
                display: flex;
                align-items: center;
                gap: 4px;
                padding: 8px;
                border: 0;
                border-radius: 8px;
                background: transparent;
                color: var(--on-dark);
                font-size: 14px;
                line-height: 20px;
                white-space: nowrap;
            }

            button:hover {
                background: var(--on-dark-hover);
            }

            @include mq-sm {
                flex-wrap: wrap;
                gap: 0;

                > button,
                :deep(.selection-menu-anchor > button) {
                    font-size: 12px;
                    padding: 8px 4px;
                }
            }
        }

        .selection-count {
            padding: 2px 8px;
            border-radius: 99px;
            background: var(--on-dark-soft);
            white-space: nowrap;
            margin-left: 4px;
        }

        .selection-menu-anchor {
            position: relative;

            @include mq-sm {
                :deep(.quick-menu) {
                    left: 0;
                }
            }
        }

        @include mq-xl-only {
            align-items: flex-start;
            flex-direction: column;
        }

        @include mq-lg {
            flex-wrap: wrap;
        }
    }
</style>
