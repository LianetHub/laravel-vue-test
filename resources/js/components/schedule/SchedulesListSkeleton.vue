<template>
    <div class="list-skeleton" role="status" aria-label="Загрузка графиков" aria-busy="true">
        <section
            v-for="group in 2"
            :key="'g-' + group"
            class="list-skeleton__group"
            aria-hidden="true"
        >
            <div class="list-skeleton__header">
                <div class="list-skeleton__title">
                    <SkeletonBlock width="140px" height="24px" radius="8px" />
                    <SkeletonBlock width="28px" height="20px" radius="99px" />
                </div>
                <SkeletonBlock width="28px" height="28px" circle />
            </div>
            <div class="list-skeleton__cards">
                <div v-for="card in 3" :key="'c-' + group + '-' + card" class="list-skeleton__card">
                    <div class="list-skeleton__card-top">
                        <SkeletonBlock width="55%" height="14px" radius="6px" />
                        <SkeletonBlock width="88px" height="26px" radius="99px" />
                    </div>
                    <SkeletonBlock width="80%" height="20px" radius="8px" />
                    <SkeletonBlock width="100%" height="12px" radius="6px" />
                    <SkeletonBlock width="100%" height="4px" radius="4px" />
                    <div class="list-skeleton__card-footer">
                        <SkeletonBlock width="72px" height="14px" radius="6px" />
                        <SkeletonBlock width="88px" height="14px" radius="6px" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
    import SkeletonBlock from "@/components/ui/SkeletonBlock.vue";
</script>

<style lang="scss" scoped>
    .list-skeleton {
        display: flex;
        flex-direction: column;
        gap: 8px;

        &__group {
            border-radius: 20px;
            background: var(--bg-surface);
        }

        &__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 64px;
            padding: 16px 24px;

            @include mq-sm {
                padding: 16px;
            }
        }

        &__title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        &__cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 348px));
            gap: 8px;
            padding: 0 16px 16px;

            @include mq-lg {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            @include mq-sm {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        &__card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            height: 220px;
            padding: 16px;
            border-radius: 12px;
            background: var(--bg-card);
        }

        &__card-top,
        &__card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        &__card-footer {
            margin-top: auto;
            padding-top: 12px;
        }
    }
</style>
