<template>
    <component
        :is="as"
        class="app-text"
        :class="[`app-text--${size}`, `app-text--${resolvedTone}`]"
    >
        <slot />
    </component>
</template>

<script setup lang="ts">
    import { computed } from "vue";

    const props = withDefaults(
        defineProps<{
            as?: "p" | "span" | "div" | "label";
            size?: "md" | "sm" | "xs";
            tone?: "primary" | "secondary" | "muted" | "brand" | "danger";
        }>(),
        {
            as: "p",
            size: "md",
            tone: "primary",
        },
    );

    const resolvedTone = computed(() => (props.tone === "muted" ? "secondary" : props.tone));
</script>

<style lang="scss" scoped>
    .app-text {
        margin: 0;

        &--md {
            font-size: 16px;
            line-height: 24px;
        }

        &--sm {
            font-size: 14px;
            line-height: 20px;
        }

        &--xs {
            font-size: 12px;
            line-height: 16px;
        }

        &--primary {
            color: var(--text-primary);
        }

        &--secondary {
            color: var(--text-secondary);
        }

        &--brand {
            color: var(--brand-strong);
        }

        &--danger {
            color: var(--danger);
        }
    }
</style>
