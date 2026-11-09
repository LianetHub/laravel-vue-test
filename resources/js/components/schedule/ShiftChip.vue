<template>
    <span class="shift-chip" :class="'shift-chip--' + colorClass">
        <span v-if="!compact" class="shift-chip__time">
            {{ shortTime(start) }} - {{ shortTime(end) }}
        </span>
        <span class="shift-chip__hours">{{ formatHours(hoursBetween(start, end)) }} ч</span>
    </span>
</template>

<script setup lang="ts">
    import { computed } from "vue";
    import { formatHours, hoursBetween } from "@/utils/dates";

    const props = defineProps<{
        start: string;
        end: string;
        color?: string | null;
        compact?: boolean;
    }>();
    const colorClass = computed(() =>
        ["cornflower", "mint", "lavanda", "brand"].includes(props.color || "")
            ? props.color
            : "brand",
    );
    function shortTime(time: string): string {
        return time.endsWith(":00") ? time.slice(0, 2) : time;
    }
</script>

<style lang="scss" scoped>
    .shift-chip {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        border-radius: 8px;
        padding: 4px;
        gap: 4px;

        &__time {
            font-size: 16px;
            line-height: 24px;
            color: var(--text-primary);
            white-space: nowrap;
        }

        &__hours {
            font-size: 12px;
            line-height: 16px;
            font-weight: 600;
        }

        &--cornflower {
            background: var(--shift-cornflower-bg);
            color: var(--shift-cornflower);
        }

        &--lavanda {
            background: var(--shift-lavanda-bg);
            color: var(--shift-lavanda);
        }

        &--mint {
            background: var(--shift-mint-bg);
            color: var(--shift-mint);
        }

        &--brand {
            background: var(--brand);
            color: var(--on-dark);

            .shift-chip__time {
                color: var(--on-dark);
            }
        }
    }
</style>
