<template>
    <article
        class="schedule-card"
        role="button"
        tabindex="0"
        :aria-label="'Открыть график ' + schedule.title"
        @click="emit('open')"
        @keydown.enter="emit('open')"
        @keydown.space.prevent="emit('open')"
    >
        <div class="schedule-card__top">
            <div class="schedule-card__address">
                <IconLocation aria-hidden="true" />
                <AppText as="span" size="xs" tone="secondary">
                    {{ schedule.address || "Адрес не указан" }}
                </AppText>
            </div>
            <StatusBadge :status="schedule.status" />
        </div>
        <AppTitle as="h3" size="md" class="schedule-card__title">
            {{ schedule.title }}
        </AppTitle>
        <div class="schedule-card__progress">
            <div class="schedule-card__progress-row">
                <span>Прогресс</span>
                <span>{{ schedule.progress }}%</span>
            </div>
            <div class="progress-track">
                <div class="progress-track__fill" :style="{ width: `${schedule.progress}%` }" />
            </div>
        </div>
        <div class="schedule-card__footer">
            <span class="schedule-card__meta">
                <IconPeople aria-hidden="true" />
                <AppText as="span" size="xs" tone="secondary">
                    {{ schedule.employees_count }} чел.
                </AppText>
            </span>
            <span class="schedule-card__meta">
                <IconCalendar aria-hidden="true" />
                <AppText as="span" size="xs" tone="secondary">
                    {{ schedule.period_end || "—" }}
                </AppText>
            </span>
        </div>
    </article>
</template>

<script setup lang="ts">
    import AppText from "@/components/ui/AppText.vue";
    import AppTitle from "@/components/ui/AppTitle.vue";
    import StatusBadge from "@/components/ui/StatusBadge.vue";
    import IconCalendar from "@/icons/calendar.svg";
    import IconLocation from "@/icons/location.svg";
    import IconPeople from "@/icons/people.svg";
    import type { Schedule } from "@/types";

    defineProps<{ schedule: Schedule }>();

    const emit = defineEmits<{ open: [] }>();
</script>

<style lang="scss" scoped>
    .schedule-card {
        display: flex;
        flex-direction: column;
        gap: 12px;
        height: 220px;
        padding: 16px;
        border-radius: 12px;
        background: var(--bg-card);
        cursor: pointer;
        min-width: 0;

        &:hover {
            box-shadow: inset 0 0 0 1px var(--brand-border);
        }

        &__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            height: 26px;
            flex-shrink: 0;
        }

        &__address {
            display: flex;
            align-items: center;
            gap: 4px;
            min-width: 0;
            color: var(--text-secondary);
        }

        :deep(.schedule-card__title) {
            height: 66px;
            flex-shrink: 0;
            overflow: hidden;
            overflow-wrap: anywhere;
        }

        &__progress {
            height: 32px;
            flex-shrink: 0;
        }

        &__progress-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        &__footer {
            display: flex;
            justify-content: space-between;
            padding-top: 12px;
        }

        &__meta {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: var(--text-secondary);
        }

        .progress-track {
            height: 4px;
            border-radius: 4px;
            background: var(--toggle-track);
            overflow: hidden;

            &__fill {
                height: 100%;
                border-radius: 4px;
                background: var(--chrome-dark);
            }
        }
    }
</style>
