<template>
    <span class="badge" :class="status === 'published' ? 'badge--published' : 'badge--draft'">
        {{ status === "published" ? "Опубликован" : "Черновик" }}
    </span>
</template>

<script setup lang="ts">
    import type { ScheduleStatus } from "@/types";

    defineProps<{ status: ScheduleStatus }>();
</script>

<style lang="scss" scoped>
    .badge {
        display: inline-flex;
        align-items: center;
        height: 26px;
        padding: 5px 8px;
        border-radius: 99px;
        color: var(--on-dark);
        font-size: 12px;
        line-height: 16px;
        font-weight: 600;
        flex-shrink: 0;

        &--published {
            background: var(--status-published);
        }

        &--draft {
            background: var(--chrome-dark);
        }
    }
</style>
