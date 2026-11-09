<template>
    <div class="app-shell">
        <AppSidebar />
        <main class="main">
            <div class="home">
                <AppTitle as="h1" size="xl">Главная</AppTitle>
                <AppText class="home__lead" size="md" tone="secondary">
                    Добро пожаловать в CRM. <br> Раздел смен, список графиков и редактор расписания.
                </AppText>
                <AppButton variant="primary" :to="{ name: 'schedules' }">
                    Перейти к сменам
                </AppButton>
            </div>
        </main>
    </div>
</template>

<script setup lang="ts">
    import AppSidebar from "@/components/layout/AppSidebar.vue";
    import AppButton from "@/components/ui/AppButton.vue";
    import AppText from "@/components/ui/AppText.vue";
    import AppTitle from "@/components/ui/AppTitle.vue";
</script>

<style lang="scss" scoped>
    .home {
        max-width: 640px;
        padding: 24px 0;

        &__lead {
            margin: 12px 0 24px;
        }
    }
</style>
