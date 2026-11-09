<template>
    <div class="editor-skeleton" role="status" aria-label="Загрузка графика" aria-busy="true">
        <header class="editor-skeleton__header">
            <div class="editor-skeleton__header-left">
                <SkeletonBlock width="120px" height="32px" radius="10px" />
                <SkeletonBlock width="140px" height="16px" radius="6px" />
            </div>
            <div class="editor-skeleton__header-actions">
                <SkeletonBlock width="140px" height="48px" radius="12px" />
                <SkeletonBlock width="120px" height="48px" radius="12px" />
            </div>
        </header>

        <div class="editor-skeleton__layout">
            <section class="editor-skeleton__board" aria-hidden="true">
                <div class="editor-skeleton__toolbar">
                    <SkeletonBlock class="editor-skeleton__search" height="48px" radius="12px" />
                    <SkeletonBlock width="301px" height="48px" radius="12px" />
                    <SkeletonBlock
                        class="editor-skeleton__period"
                        width="180px"
                        height="32px"
                        radius="8px"
                    />
                </div>

                <div class="editor-skeleton__grid">
                    <div class="editor-skeleton__grid-header">
                        <SkeletonBlock width="140px" height="16px" />
                        <SkeletonBlock
                            v-for="day in 7"
                            :key="'h-' + day"
                            width="40px"
                            height="28px"
                            radius="8px"
                        />
                    </div>
                    <div v-for="row in 6" :key="'r-' + row" class="editor-skeleton__grid-row">
                        <div class="editor-skeleton__employee">
                            <SkeletonBlock width="36px" height="36px" circle />
                            <div class="editor-skeleton__employee-text">
                                <SkeletonBlock width="70%" height="14px" />
                                <SkeletonBlock width="40%" height="12px" />
                            </div>
                        </div>
                        <SkeletonBlock
                            v-for="day in 7"
                            :key="'c-' + row + '-' + day"
                            height="40px"
                            radius="8px"
                        />
                    </div>
                    <div class="editor-skeleton__coverage">
                        <SkeletonBlock width="90px" height="16px" />
                        <SkeletonBlock
                            v-for="day in 7"
                            :key="'cov-' + day"
                            width="28px"
                            height="20px"
                            radius="6px"
                        />
                    </div>
                </div>
            </section>

            <aside class="editor-skeleton__panel" aria-hidden="true">
                <SkeletonBlock width="140px" height="18px" radius="6px" />
                <SkeletonBlock v-for="card in 4" :key="'t-' + card" height="64px" radius="8px" />
                <SkeletonBlock
                    width="110px"
                    height="18px"
                    radius="6px"
                    class="editor-skeleton__panel-gap"
                />
                <div class="editor-skeleton__times">
                    <SkeletonBlock height="48px" radius="8px" />
                    <SkeletonBlock height="48px" radius="8px" />
                </div>
                <SkeletonBlock height="48px" radius="12px" />
            </aside>
        </div>
    </div>
</template>

<script setup lang="ts">
    import SkeletonBlock from "@/components/ui/SkeletonBlock.vue";
</script>

<style lang="scss" scoped>
    .editor-skeleton {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;

        &__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            height: 48px;
            margin-bottom: 24px;
            flex-shrink: 0;

            @include mq-sm {
                height: auto;
                flex-direction: column;
                align-items: stretch;
            }
        }

        &__header-left,
        &__header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        &__header-actions {
            gap: 4px;

            @include mq-sm {
                width: 100%;

                .skeleton-block {
                    flex: 1;
                }
            }
        }

        &__layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 335px;
            gap: 8px;
            align-items: stretch;
            flex: 1;
            min-height: 0;

            @include mq-lg {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        &__board {
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: var(--bg-surface);
            border-radius: 20px;
            padding: 12px;
            min-width: 0;
            min-height: 0;
            height: 100%;
        }

        &__toolbar {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
            min-height: 48px;

            @include mq-lg {
                flex-wrap: wrap;
            }

            @include mq-sm {
                .editor-skeleton__search {
                    flex-basis: 100%;
                }
            }
        }

        &__search {
            flex: 1;
            min-width: 160px;
        }

        &__period {
            margin-left: auto;

            @include mq-lg {
                margin-left: 0;
            }
        }

        &__grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        &__grid-header,
        &__grid-row,
        &__coverage {
            display: grid;
            grid-template-columns: 220px repeat(7, minmax(0, 1fr));
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }

        &__grid-header,
        &__coverage {
            height: 48px;
        }

        &__grid-row {
            height: 72px;
            padding: 0 4px;
            border-radius: 8px;
            background: var(--bg-card);
        }

        &__employee {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding-inline: 8px;
        }

        &__employee-text {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 0;
        }

        &__coverage {
            margin-top: auto;
        }

        &__panel {
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: var(--bg-surface);
            border-radius: 12px;
            padding: 16px;
            min-width: 0;
            min-height: 0;
            height: 100%;

            @include mq-lg {
                height: auto;
            }
        }

        &__panel-gap {
            margin-top: 16px;
        }

        &__times {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
    }
</style>
