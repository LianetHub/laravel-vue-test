<template>
    <aside class="side-panel">
        <AppTitle as="h2" size="sm" class="side-panel__title">Шаблоны смен</AppTitle>
        <div class="template-list">
            <button
                v-for="(template, index) in store.templates"
                :key="template.id"
                type="button"
                class="template-card"
                :class="[
                    'template-card--' + template.color,
                    { 'is-selected': store.selectedTemplateId === template.id },
                ]"
                :data-template-id="template.id"
                :aria-pressed="store.selectedTemplateId === template.id"
                :disabled="!store.editable"
                :draggable="store.editable"
                @click="store.chooseTemplate(template)"
                @dragstart="drag($event, template.id)"
                @dragend="store.dragOverKey = null"
            >
                <span class="template-card__name">{{ template.name }}</span>
                <span class="template-card__time">
                    {{ template.start_time }} - {{ template.end_time }} ·
                    {{ formatHours(hoursBetween(template.start_time, template.end_time)) }}
                    ч
                </span>
                <span class="template-card__number">{{ index + 1 }}</span>
            </button>
            <AppText v-if="!store.templates.length" size="sm" tone="secondary">
                Нет доступных шаблонов. Можно назначить свою смену.
            </AppText>
        </div>
        <AppText v-if="store.selectedTemplateId" class="template-hint" size="xs" tone="brand">
            Выберите свободные ячейки.
            <button type="button" class="text-button" @click="store.selectedTemplateId = null">
                Отменить
            </button>
        </AppText>
        <AppTitle as="h2" size="sm" class="side-panel__title">Своя смена</AppTitle>
        <form class="custom-shift" novalidate @submit.prevent="applyCustom">
            <div class="time-fields">
                <AppInput
                    id="custom-start"
                    v-model="store.customStart"
                    label="Начало"
                    type="time"
                    size="lg"
                    :disabled="!store.editable"
                    :error="store.customErrors.start"
                />
                <AppInput
                    id="custom-end"
                    v-model="store.customEnd"
                    label="Конец"
                    type="time"
                    size="lg"
                    :disabled="!store.editable"
                    :error="store.customErrors.end"
                />
            </div>
            <AppButton
                class="custom-apply"
                variant="secondary"
                :disabled="!store.editable || !store.selectedCells.length"
                type="submit"
            >
                <IconPlus aria-hidden="true" />
                Поставить в выделенные
            </AppButton>
        </form>
    </aside>
</template>

<script setup lang="ts">
    import AppButton from "@/components/ui/AppButton.vue";
    import AppInput from "@/components/ui/AppInput.vue";
    import AppText from "@/components/ui/AppText.vue";
    import AppTitle from "@/components/ui/AppTitle.vue";
    import IconPlus from "@/icons/plus.svg";
    import { useScheduleStore } from "@/stores/schedule";
    import { formatHours, hoursBetween } from "@/utils/dates";

    const store = useScheduleStore();

    function drag(event: DragEvent, id: number) {
        if (!store.editable || !event.dataTransfer) {
            event.preventDefault();
            return;
        }
        event.dataTransfer.setData("application/x-shift-template", String(id));
        event.dataTransfer.effectAllowed = "copy";
    }

    function applyCustom() {
        if (store.assignCustomToCells([...store.selectedCells])) store.clearSelection();
    }
</script>

<style lang="scss" scoped>
    .side-panel {
        display: flex;
        flex-direction: column;
        background: var(--bg-surface);
        border-radius: 12px;
        padding: 16px;
        min-width: 0;
        min-height: 0;
        height: 100%;
        overflow: hidden;

        :deep(.side-panel__title) {
            margin-bottom: 8px;
            flex-shrink: 0;
        }

        .template-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 24px;
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar) transparent;
        }

        .custom-shift {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-shrink: 0;
        }

        @include mq-lg {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 24px;
            height: auto;
            overflow: visible;

            > :deep(.side-panel__title:first-child) {
                grid-column: 1;
                grid-row: 1;
            }

            > .template-list {
                grid-column: 1;
                grid-row: 2 / 4;
                margin: 0;
                overflow: visible;
                min-height: 0;
            }

            > :deep(.side-panel__title:not(:first-child)) {
                grid-column: 2;
                grid-row: 1;
            }

            > .custom-shift {
                grid-column: 2;
                grid-row: 2;
                align-self: start;
            }
        }

        @include mq-sm {
            display: block;

            > .template-list {
                margin-bottom: 24px;
            }
        }
    }

    .template-card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-height: 64px;
        padding: 8px 12px;
        border: 0;
        border-left: 3px solid var(--brand-border);
        border-radius: 8px;
        background: var(--bg-muted);
        text-align: left;
        cursor: grab;

        &--cornflower {
            border-left-color: var(--brand-border);
        }

        &--lavanda {
            border-left-color: var(--shift-lavanda-border);
        }

        &--mint {
            border-left-color: var(--shift-mint-border);
        }

        &--brand {
            border-left-color: var(--brand);
        }

        &:hover:not(:disabled),
        &.is-selected {
            background: var(--brand-soft);
            box-shadow: inset 0 0 0 1px var(--brand-border);
        }

        &:active {
            cursor: grabbing;
        }

        &__name {
            font-size: 16px;
            line-height: 24px;
            font-weight: 600;
        }

        &__time {
            font-size: 16px;
            line-height: 24px;
            font-weight: 400;
        }

        &__number {
            position: absolute;
            right: 8px;
            top: 8px;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-surface);
            border-radius: 4px;
            font-size: 12px;
            line-height: 16px;
            font-weight: 600;
        }
    }

    :deep(.template-hint) {
        margin: -12px 0 16px;
        line-height: 18px;
        flex-shrink: 0;

        @include mq-lg {
            grid-column: 1 / -1;
            margin: 0;
        }
    }

    :deep(.custom-apply) {
        width: 100%;
        padding: 12px 8px;

        &:disabled {
            opacity: 1;
            color: var(--brand-strong);
        }
    }
</style>
