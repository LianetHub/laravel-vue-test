<template>
    <div class="app-shell">
        <AppSidebar />
        <main class="main">
            <div class="not-found">
                <AppTitle as="h1" size="xl">Страница не найдена</AppTitle>
                <AppText class="not-found__subtitle" size="md" tone="secondary">
                    Такой страницы нет или ссылка устарела. Вернитесь на главную или откройте раздел
                    смен.
                </AppText>
                <div class="not-found__actions">
                    <AppButton variant="primary" :to="{ name: 'home' }">На главную</AppButton>
                    <AppButton variant="secondary" :to="{ name: 'schedules' }">К сменам</AppButton>
                </div>
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
    .main {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .not-found {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        max-width: 480px;
        padding: 24px 16px;
    }

    .not-found__subtitle {
        margin: 12px 0 24px;
    }

    .not-found__actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 8px;
    }
</style>
