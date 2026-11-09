<template>
    <div class="segmented-control" role="group" :aria-label="ariaLabel">
        <span class="segmented-control__thumb" :style="thumbStyle" aria-hidden="true" />
        <button
            v-for="option in options"
            :key="String(option.value)"
            type="button"
            class="segmented-control__item"
            :class="{ 'is-active': modelValue === option.value }"
            :aria-pressed="modelValue === option.value"
            @click="emit('update:modelValue', option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>

<script setup lang="ts" generic="T extends string | number">
    import { computed } from "vue";

    const props = defineProps<{
        modelValue: T;
        options: { value: T; label: string }[];
        ariaLabel?: string;
    }>();

    const emit = defineEmits<{
        "update:modelValue": [value: T];
    }>();

    const activeIndex = computed(() => {
        const index = props.options.findIndex((option) => option.value === props.modelValue);
        return Math.max(0, index);
    });

    const thumbStyle = computed(() => {
        const count = Math.max(1, props.options.length);
        return {
            width: `calc((100% - 4px) / ${count})`,
            transform: `translateX(${activeIndex.value * 100}%)`,
        };
    });
</script>

<style lang="scss" scoped>
    .segmented-control {
        position: relative;
        display: flex;
        flex: 0 0 301px;
        height: 48px;
        padding: 2px;
        border-radius: 12px;
        background: var(--bg-muted);
        min-width: 0;

        &__thumb {
            position: absolute;
            top: 2px;
            left: 2px;
            height: calc(100% - 4px);
            border-radius: 10px;
            background: var(--chrome);
            pointer-events: none;
            transition:
                transform 200ms ease,
                width 200ms ease;

            @media (prefers-reduced-motion: reduce) {
                transition: none;
            }
        }

        &__item {
            position: relative;
            z-index: 1;
            flex: 1;
            border: 0;
            background: transparent;
            border-radius: 10px;
            padding: 8px 12px;
            font-weight: 600;
            color: var(--text-primary);
            transition: color 200ms ease;

            &:hover:not(.is-active) {
                background: var(--bg-hover);
            }

            &.is-active {
                color: var(--on-dark);
            }

            @media (prefers-reduced-motion: reduce) {
                transition: none;
            }
        }
    }
</style>
