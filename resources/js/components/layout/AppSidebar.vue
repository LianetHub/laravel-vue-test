<template>
    <aside class="sidebar" :class="{ 'is-collapsed': layout.collapsed }">
        <div class="sidebar__brand">
            <AppTitle as="span" size="md" class="sidebar__brand-title">CRM</AppTitle>
            <div class="notification-control">
                <button
                    class="icon-button"
                    type="button"
                    aria-label="Уведомления"
                    :aria-expanded="notifications"
                    @click="notifications = !notifications"
                >
                    <IconBell aria-hidden="true" />
                </button>
                <div v-if="notifications" class="notification-popover" role="status">
                    Новых уведомлений нет
                </div>
            </div>
        </div>
        <nav class="sidebar__nav" aria-label="Основное меню">
            <RouterLink
                class="sidebar__item"
                :to="{ name: 'home' }"
                title="Главная"
                active-class=""
                exact-active-class="is-active"
            >
                <IconHome aria-hidden="true" />
                <span>Главная</span>
            </RouterLink>
            <RouterLink
                class="sidebar__item"
                :to="{ name: 'schedules' }"
                title="Смены"
                active-class="is-active"
                exact-active-class="is-active"
            >
                <IconShifts aria-hidden="true" />
                <span>Смены</span>
            </RouterLink>
        </nav>
        <button
            class="sidebar__item sidebar__collapse"
            type="button"
            :aria-expanded="!layout.collapsed"
            :aria-label="layout.collapsed ? 'Развернуть меню' : 'Свернуть меню'"
            @click="layout.toggle()"
        >
            <IconSidebarCollapse aria-hidden="true" />
            <span>{{ layout.collapsed ? "Развернуть меню" : "Свернуть меню" }}</span>
        </button>
    </aside>
</template>

<script setup lang="ts">
    import { ref } from "vue";
    import { RouterLink } from "vue-router";
    import AppTitle from "@/components/ui/AppTitle.vue";
    import IconBell from "@/icons/bell.svg";
    import IconHome from "@/icons/home.svg";
    import IconShifts from "@/icons/shifts.svg";
    import IconSidebarCollapse from "@/icons/sidebar-collapse.svg";
    import { useLayoutStore } from "@/stores/layout";

    const layout = useLayoutStore();
    const notifications = ref(false);
</script>

<style lang="scss" scoped>
    .sidebar {
        $root: &;

        position: sticky;
        top: 0;
        height: 100vh;
        width: var(--sidebar-width);
        flex-shrink: 0;
        border-right: 1px solid var(--border);
        padding: 16px 15px 16px 14px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        z-index: 15;
        overflow: hidden;

        @include mq-min($bp-md + 1) {
            transition:
                width 200ms ease,
                padding 200ms ease;

            @media (prefers-reduced-motion: reduce) {
                transition: none;
            }
        }

        &__brand {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
        }

        &__nav {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
        }

        &__item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 40px;
            padding: 8px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
            color: var(--text-primary);

            span {
                display: inline-block;
                max-width: 200px;
                opacity: 1;
                overflow: hidden;
                transition:
                    opacity 160ms ease,
                    max-width 200ms ease;

                @media (prefers-reduced-motion: reduce) {
                    transition: none;
                }
            }

            &:hover {
                background: var(--bg-hover);
                color: var(--brand);
            }

            &.is-active {
                background: var(--bg-surface);
            }
        }

        &__collapse {
            margin-top: auto;
        }

        .notification-control {
            position: relative;
            flex-shrink: 0;
            max-width: 40px;
            opacity: 1;
            overflow: hidden;
            transition:
                opacity 160ms ease,
                max-width 200ms ease;

            @media (prefers-reduced-motion: reduce) {
                transition: none;
            }
        }

        .notification-popover {
            position: absolute;
            top: 44px;
            right: 0;
            width: 240px;
            background: var(--bg-surface);
            border: 1px solid var(--border);
            padding: 16px;
            border-radius: 12px;
            box-shadow: 0 8px 30px var(--shadow-12);
            font-size: 14px;
            z-index: 50;
        }

        :deep(#{$root}__brand-title) {
            transition: font-size 200ms ease;

            @media (prefers-reduced-motion: reduce) {
                transition: none;
            }
        }

        &.is-collapsed {
            width: 72px;
            padding-inline: 15px;

            #{$root}__item {
                span {
                    max-width: 0;
                    opacity: 0;
                    pointer-events: none;
                }
            }

            .notification-control {
                max-width: 0;
                opacity: 0;
                pointer-events: none;
            }

            :deep(#{$root}__brand-title) {
                font-size: 16px;
            }
        }

        @include mq-md {
            &,
            &.is-collapsed {
                position: relative;
                width: 100%;
                height: auto;
                padding: 8px 16px;
                border-right: 0;
                border-bottom: 1px solid var(--border);
                flex-direction: row;
                align-items: center;
                gap: 12px;
                overflow: visible;
                transition: none;
            }

            #{$root}__brand {
                gap: 12px;
            }

            .notification-control {
                display: none;
                max-width: none;
                opacity: 1;
                transition: none;
            }

            #{$root}__nav {
                flex-direction: row;
                justify-content: flex-end;
            }

            #{$root}__item {
                width: auto;
                gap: 12px;

                span {
                    max-width: none;
                    opacity: 1;
                    transition: none;
                }
            }

            #{$root}__collapse {
                margin-top: 0;
                width: 40px;
                flex-shrink: 0;

                span {
                    display: none;
                    max-width: 0;
                    opacity: 0;
                }
            }

            &.is-collapsed #{$root}__item span {
                display: none;
                max-width: 0;
                opacity: 0;
            }
        }

        @include mq-sm {
            #{$root}__nav {
                flex: 1;

                #{$root}__item span {
                    display: none;
                    max-width: 0;
                    opacity: 0;
                }
            }
        }
    }
</style>
