<template>
    <component :is="as" class="app-title" :class="[`app-title--${size}`]">
        <slot />
    </component>
</template>

<script setup lang="ts">
    withDefaults(
        defineProps<{
            as?: "h1" | "h2" | "h3" | "h4" | "div" | "span";
            size?: "xl" | "lg" | "md" | "sm";
        }>(),
        {
            as: "h1",
            size: "xl",
        },
    );
</script>

<style lang="scss" scoped>
    .app-title {
        margin: 0;
        color: var(--text-primary);

        &--xl {
            font-size: 32px;
            line-height: 32px;
            font-weight: 500;
            letter-spacing: -1px;

            @include mq-sm {
                font-size: 28px;
                line-height: 32px;
            }
        }

        &--lg {
            font-size: 24px;
            line-height: 32px;
            font-weight: 500;
            letter-spacing: -0.5px;
        }

        &--md {
            font-size: 20px;
            line-height: 24px;
            font-weight: 600;
            letter-spacing: -0.3px;
        }

        &--sm {
            font-size: 16px;
            line-height: 24px;
            font-weight: 600;
        }
    }
</style>
