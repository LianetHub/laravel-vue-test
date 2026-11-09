<template>
    <span class="app-counter">
        <slot />
    </span>
</template>

<style lang="scss" scoped>
    .app-counter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 21px;
        height: 20px;
        padding: 2px 8px;
        border-radius: 99px;
        background: var(--bg-muted);
        font-size: 12px;
        line-height: 16px;
        font-weight: 400;
        letter-spacing: 0;
    }
</style>
