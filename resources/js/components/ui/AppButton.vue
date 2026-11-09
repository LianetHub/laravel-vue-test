<template>
    <component
        :is="to ? RouterLink : 'button'"
        class="app-button"
        :class="[`app-button--${variant}`]"
        :to="to || undefined"
        :type="to ? undefined : type"
        :disabled="to ? undefined : disabled"
        :aria-disabled="to && disabled ? true : undefined"
    >
        <slot />
    </component>
</template>

<script setup lang="ts">
    import { RouterLink, type RouteLocationRaw } from "vue-router";

    withDefaults(
        defineProps<{
            variant?: "primary" | "secondary" | "danger";

            type?: "button" | "submit" | "reset";
            disabled?: boolean;
            to?: RouteLocationRaw;
        }>(),
        {
            variant: "primary",
            type: "button",
            disabled: false,
        },
    );
</script>

<style lang="scss" scoped>
    .app-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 48px;
        padding: 12px 16px;
        border: 0;
        border-radius: 8px;
        font-size: 16px;
        line-height: 24px;
        font-weight: 600;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
        color: inherit;

        &:disabled,
        &[aria-disabled="true"] {
            opacity: 0.65;
            cursor: not-allowed;
        }

        &--primary {
            background: var(--brand);
            color: var(--on-dark);

            &:hover:not(:disabled):not([aria-disabled="true"]) {
                background: var(--brand-hover);
            }
        }

        &--secondary {
            background: var(--brand-soft);
            color: var(--brand-strong);

            &:hover:not(:disabled):not([aria-disabled="true"]) {
                background: var(--brand-secondary-hover);
            }
        }

        &--danger {
            color: var(--danger);
            background: var(--danger-bg);

            &:hover:not(:disabled):not([aria-disabled="true"]) {
                background: var(--danger-bg-hover);
            }
        }
    }
</style>
