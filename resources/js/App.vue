<template>
    <RouterView />
    <AppToastHost />
</template>

<script setup lang="ts">
    import { RouterView } from "vue-router";
    import AppToastHost from "@/components/ui/AppToastHost.vue";
</script>

<style lang="scss">
    @font-face {
        font-family: Manrope;
        src: url("../../public/fonts/manrope-400.ttf") format("truetype");
        font-weight: 400;
        font-display: swap;
    }

    @font-face {
        font-family: Manrope;
        src: url("../../public/fonts/manrope-500.ttf") format("truetype");
        font-weight: 500;
        font-display: swap;
    }

    @font-face {
        font-family: Manrope;
        src: url("../../public/fonts/manrope-600.ttf") format("truetype");
        font-weight: 600;
        font-display: swap;
    }

    @font-face {
        font-family: Manrope;
        src: url("../../public/fonts/manrope-700.ttf") format("truetype");
        font-weight: 700;
        font-display: swap;
    }

    :root {
        --bg-page: #{$color-page};
        --bg-surface: #{$color-surface};
        --bg-muted: #{$color-muted};
        --bg-card: #{$color-card};
        --bg-hover: #{$color-hover};
        --bg-list-hover: #{$color-list-hover};
        --border: #{$color-border};
        --scrollbar: #{$color-scrollbar};
        --text-primary: #{$color-text-primary};
        --text-secondary: #{$color-text-secondary};
        --text-muted: #{$color-text-muted};
        --brand: #{$color-brand};
        --brand-hover: #{$color-brand-hover};
        --brand-soft: #{$color-brand-soft};
        --brand-strong: #{$color-brand-strong};
        --brand-border: #{$color-brand-border};
        --brand-selected: #{$color-brand-selected};
        --brand-secondary-hover: #{$color-brand-secondary-hover};
        --status-published: #{$color-status-published};
        --chrome: #{$color-chrome};
        --chrome-dark: #{$color-chrome-dark};
        --toggle-track: #{$color-toggle-track};
        --on-dark: #{$color-on-dark};
        --on-dark-hover: #{$color-on-dark-hover};
        --on-dark-soft: #{$color-on-dark-soft};
        --shift-cornflower-bg: #{$color-shift-cornflower-bg};
        --shift-cornflower: #{$color-shift-cornflower};
        --shift-lavanda-bg: #{$color-shift-lavanda-bg};
        --shift-lavanda: #{$color-shift-lavanda};
        --shift-lavanda-border: #{$color-shift-lavanda-border};
        --shift-mint-bg: #{$color-shift-mint-bg};
        --shift-mint: #{$color-shift-mint};
        --shift-mint-border: #{$color-shift-mint-border};
        --danger: #{$color-danger};
        --danger-border: #{$color-danger-border};
        --danger-bg: #{$color-danger-bg};
        --danger-bg-hover: #{$color-danger-bg-hover};
        --danger-cell: #{$color-danger-cell};
        --danger-cell-bg: #{$color-danger-cell-bg};
        --toast-error-bg: #{$color-toast-error-bg};
        --toast-error: #{$color-toast-error};
        --toast-error-border: #{$color-toast-error-border};
        --toast-success-bg: #{$color-toast-success-bg};
        --toast-success: #{$color-toast-success};
        --toast-success-border: #{$color-toast-success-border};
        --shadow-12: #{$color-shadow-12};
        --shadow-15: #{$color-shadow-15};
        --shadow-20: #{$color-shadow-20};
        --shadow-25: #{$color-shadow-25};
        --overlay: #{$color-overlay};
        --sidebar-width: #{$sidebar-width};
        --font: #{$font-family};
    }
    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: var(--text-primary);
        background: var(--bg-page);
        font: 400 16px/24px var(--font);
        -webkit-font-smoothing: antialiased;
    }

    button,
    input,
    select {
        font: inherit;
    }

    button,
    a,
    input,
    select {
        -webkit-tap-highlight-color: transparent;
    }

    button {
        cursor: pointer;
        color: inherit;

        &:disabled {
            cursor: not-allowed;
        }
    }

    button:focus-visible,
    a:focus-visible,
    [tabindex]:focus-visible {
        outline: 2px solid var(--brand);
        outline-offset: 3px;
    }

    a {
        color: inherit;
        text-decoration: none;
    }

    img {
        display: block;
        flex-shrink: 0;
    }

    button img,
    button svg {
        pointer-events: none;
    }

    svg {
        display: block;
        flex-shrink: 0;
    }

    h1,
    h2,
    h3,
    p {
        margin: 0;
    }

    .app-shell {
        display: flex;
        min-height: 100vh;

        @include mq-md {
            flex-direction: column;
        }
    }

    .main {
        min-width: 0;
        flex: 1;
        padding: 30px;

        @include mq-md {
            padding: 20px 16px;
        }
    }

    .icon-button {
        border: 0;
        background: transparent;
        border-radius: 8px;
        padding: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--text-primary);

        &:hover {
            background: var(--bg-hover);
            color: var(--brand);
        }
    }
    .text-button {
        border: 0;
        background: transparent;
        padding: 0 4px;
        color: inherit;

        &:hover {
            text-decoration: underline;
        }
    }

    .time-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .empty-state,
    .loading-state {
        padding: 40px 24px;
        text-align: center;
        color: var(--text-secondary);
    }

    .empty-state {
        .app-title {
            margin-bottom: 12px;
        }

        .app-button {
            margin: 16px 8px 0;
        }

        &--panel {
            background: var(--bg-surface);
            border-radius: 20px;
        }
    }

    .quick-menu {
        position: absolute;
        top: 100%;
        left: 0;
        width: 250px;
        padding: 8px;
        background: var(--bg-surface);
        color: var(--text-primary);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: 0 8px 24px var(--shadow-20);
        z-index: 20;
        font-size: 14px;
        line-height: 20px;
        text-align: left;

        p {
            padding: 8px;
            color: var(--text-secondary);
            font-size: 12px;
            line-height: 16px;
        }

        button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border: 0;
            border-radius: 6px;
            padding: 10px 8px;
            width: 100%;
            background: transparent;
            color: var(--text-primary);
            font-size: 14px;

            &:hover {
                background: var(--brand-soft);
            }
        }

        small {
            color: var(--text-secondary);
        }
    }

    .date-quick-menu {
        position: fixed;
        z-index: 40;
        max-width: calc(100vw - 16px);
        overflow-y: auto;
    }
</style>
