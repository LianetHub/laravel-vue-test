<template>
    <div class="toast-host" aria-live="polite">
        <div
            v-for="item in toast.items"
            :key="item.id"
            class="toast"
            :class="`toast--${item.tone}`"
            :role="item.tone === 'error' ? 'alert' : 'status'"
        >
            {{ item.message }}
            <button
                class="text-button"
                type="button"
                aria-label="Закрыть сообщение"
                @click="toast.dismiss(item.id)"
            >
                ×
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { useToastStore } from "@/stores/toast";

    const toast = useToastStore();
</script>

<style lang="scss" scoped>
    .toast-host {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 50;
        display: flex;
        flex-direction: column-reverse;
        gap: 8px;
        max-width: min(480px, calc(100vw - 32px));
        pointer-events: none;

        @include mq-sm {
            right: 16px;
            bottom: 16px;
        }
    }

    .toast {
        pointer-events: auto;
        padding: 14px 16px;
        border-radius: 12px;
        box-shadow: 0 6px 30px var(--shadow-15);
        font-size: 14px;
        line-height: 20px;
        display: flex;
        gap: 12px;
        align-items: center;

        &--error {
            background: var(--toast-error-bg);
            color: var(--toast-error);
            border: 1px solid var(--toast-error-border);
        }

        &--success {
            background: var(--toast-success-bg);
            color: var(--toast-success);
            border: 1px solid var(--toast-success-border);
        }
    }
</style>
