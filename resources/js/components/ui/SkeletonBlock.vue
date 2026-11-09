<template>
    <span
        class="skeleton-block"
        :class="{ 'skeleton-block--circle': circle }"
        :style="style"
        aria-hidden="true"
    />
</template>

<script setup lang="ts">
    import { computed } from "vue";

    const props = withDefaults(
        defineProps<{
            width?: string | number;
            height?: string | number;
            radius?: string | number;
            circle?: boolean;
        }>(),
        {
            width: "100%",
            height: "16px",
            radius: "8px",
            circle: false,
        },
    );

    function toCssSize(value: string | number): string {
        return typeof value === "number" ? `${value}px` : value;
    }

    const style = computed(() => ({
        width: toCssSize(props.width),
        height: toCssSize(props.height),
        borderRadius: props.circle ? "50%" : toCssSize(props.radius),
    }));
</script>

<style lang="scss" scoped>
    .skeleton-block {
        display: block;
        flex-shrink: 0;
        background: linear-gradient(
            90deg,
            var(--bg-hover) 0%,
            var(--bg-muted) 45%,
            var(--bg-hover) 90%
        );
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.4s ease-in-out infinite;

        @media (prefers-reduced-motion: reduce) {
            animation: none;
            background: var(--bg-hover);
        }
    }

    @keyframes skeleton-shimmer {
        0% {
            background-position: 100% 0;
        }

        100% {
            background-position: -100% 0;
        }
    }
</style>
