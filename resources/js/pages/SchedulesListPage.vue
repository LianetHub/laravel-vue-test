<template>
    <div class="app-shell">
        <AppSidebar />
        <main class="main">
            <div class="page-header">
                <div>
                    <AppTitle as="h1" size="xl">Все графики</AppTitle>
                    <AppText class="page-header__subtitle" size="md" tone="secondary">
                        Создавайте, редактируйте и публикуйте графики по точкам региона
                    </AppText>
                </div>
                <AppButton variant="primary" :disabled="creating" @click="createOpen = true">
                    <IconPlus aria-hidden="true" />
                    Создать график
                </AppButton>
            </div>

            <div class="filter-row">
                <AppInput v-model="search" type="search" tone="surface" placeholder="Поиск по названию">
                    <template #icon>
                        <IconSearch />
                    </template>
                </AppInput>
                <label class="select-like select-like--control">
                    <select v-model="statusFilter" aria-label="Фильтр по статусу">
                        <option value="all">Статус</option>
                        <option value="published">Опубликован</option>
                        <option value="draft">Черновик</option>
                    </select>
                    <IconChevronDown class="select-like__chevron" aria-hidden="true" />
                </label>
            </div>

            <SchedulesListSkeleton v-if="list.loading && !list.loaded" />
            <div v-else-if="list.error" class="empty-state empty-state--panel" role="alert">
                {{ list.error }}
                <AppButton variant="secondary" @click="ensureLoaded">Повторить</AppButton>
            </div>
            <template v-else>
                <section v-if="showPublished" class="status-group">
                    <button
                        class="status-group__header"
                        type="button"
                        :aria-expanded="publishedOpen"
                        @click="publishedOpen = !publishedOpen"
                    >
                        <div class="status-group__title">
                            <AppTitle as="span" size="md">Опубликован</AppTitle>
                            <AppCounter>{{ published.length }}</AppCounter>
                        </div>
                        <span class="status-group__toggle">
                            <component
                                :is="publishedOpen ? IconChevronUp : IconChevronDown"
                                aria-hidden="true"
                            />
                        </span>
                    </button>
                    <div v-if="publishedOpen" class="schedule-cards">
                        <ScheduleCard
                            v-for="item in published"
                            :key="item.id"
                            :schedule="item"
                            @open="openSchedule(item.id)"
                        />
                        <div v-if="published.length === 0" class="empty-state">
                            Нет опубликованных графиков
                        </div>
                    </div>
                </section>

                <section v-if="showDrafts" class="status-group">
                    <button
                        class="status-group__header"
                        type="button"
                        :aria-expanded="draftOpen"
                        @click="draftOpen = !draftOpen"
                    >
                        <div class="status-group__title">
                            <AppTitle as="span" size="md">Черновик</AppTitle>
                            <AppCounter>{{ drafts.length }}</AppCounter>
                        </div>
                        <span class="status-group__toggle">
                            <component
                                :is="draftOpen ? IconChevronUp : IconChevronDown"
                                aria-hidden="true"
                            />
                        </span>
                    </button>
                    <div v-if="draftOpen" class="schedule-cards">
                        <ScheduleCard
                            v-for="item in drafts"
                            :key="item.id"
                            :schedule="item"
                            @open="openSchedule(item.id)"
                        />
                        <div v-if="drafts.length === 0" class="empty-state">Нет черновиков</div>
                    </div>
                </section>

                <div v-if="filtered.length === 0" class="empty-state empty-state--panel">
                    Графики не найдены. Измените поиск или фильтр.
                </div>
            </template>

            <ModalDialog
                v-if="createOpen"
                title="Создать график"
                title-id="create-schedule-title"
                :busy="creating"
                @close="closeCreate"
            >
                <form class="modal__body" @submit.prevent="submitCreate">
                    <AppInput
                        id="schedule-title"
                        v-model="createForm.title"
                        label="Название точки"
                        size="lg"
                        required
                        maxlength="255"
                        placeholder="Например, ТЦ Метрополис"
                    />
                    <AppInput
                        id="schedule-address"
                        v-model="createForm.address"
                        label="Адрес"
                        size="lg"
                        maxlength="255"
                        placeholder="Улица, дом"
                    />
                    <div v-if="createError" class="modal__error">
                        {{ createError }}
                    </div>
                    <div class="modal__actions">
                        <AppButton variant="secondary" :disabled="creating" @click="closeCreate">
                            Отмена
                        </AppButton>
                        <AppButton
                            variant="primary"
                            type="submit"
                            :disabled="creating || !createForm.title.trim()"
                        >
                            {{ creating ? "Создание…" : "Создать" }}
                        </AppButton>
                    </div>
                </form>
            </ModalDialog>
        </main>
    </div>
</template>

<script setup lang="ts">
    import { computed, onMounted, ref, watch } from "vue";
    import { useRoute, useRouter } from "vue-router";
    import { createSchedule } from "@/api/schedules";
    import AppSidebar from "@/components/layout/AppSidebar.vue";
    import ScheduleCard from "@/components/schedule/ScheduleCard.vue";
    import SchedulesListSkeleton from "@/components/schedule/SchedulesListSkeleton.vue";
    import AppButton from "@/components/ui/AppButton.vue";
    import AppCounter from "@/components/ui/AppCounter.vue";
    import AppInput from "@/components/ui/AppInput.vue";
    import AppText from "@/components/ui/AppText.vue";
    import AppTitle from "@/components/ui/AppTitle.vue";
    import ModalDialog from "@/components/ui/ModalDialog.vue";
    import IconChevronDown from "@/icons/chevron-down.svg";
    import IconChevronUp from "@/icons/chevron-up.svg";
    import IconPlus from "@/icons/plus.svg";
    import IconSearch from "@/icons/search.svg";
    import { useSchedulesListStore } from "@/stores/schedulesList";
    import type { ScheduleStatus } from "@/types";

    type StatusFilter = "all" | ScheduleStatus;

    const router = useRouter();

    const route = useRoute();
    const list = useSchedulesListStore();

    const search = ref("");
    const statusFilter = ref<StatusFilter>("all");
    const publishedOpen = ref(true);
    const draftOpen = ref(false);

    const createOpen = ref(false);
    const creating = ref(false);
    const createError = ref<string | null>(null);
    const createForm = ref({ title: "", address: "" });

    const filtered = computed(() => {
        const q = search.value.trim().toLowerCase();

        return list.schedules.filter((s) => {
            const matchesStatus = statusFilter.value === "all" || s.status === statusFilter.value;
            if (!matchesStatus) return false;
            if (!q) return true;

            const title = s.title.toLowerCase();
            const address = (s.address ?? "").toLowerCase();
            return title.includes(q) || address.includes(q);
        });
    });

    const published = computed(() => filtered.value.filter((s) => s.status === "published"));
    const drafts = computed(() => filtered.value.filter((s) => s.status === "draft"));
    const showPublished = computed(
        () => statusFilter.value === "all" || statusFilter.value === "published",
    );
    const showDrafts = computed(
        () => statusFilter.value === "all" || statusFilter.value === "draft",
    );

    function openSchedule(id: number): void {
        router.push({ name: "schedule-editor", params: { id: String(id) } });
    }

    function closeCreate(): void {
        if (creating.value) return;
        createOpen.value = false;
        createError.value = null;
        createForm.value = { title: "", address: "" };
    }

    async function submitCreate(): Promise<void> {
        const title = createForm.value.title.trim();
        if (!title) return;

        creating.value = true;
        createError.value = null;

        try {
            const schedule = await createSchedule({
                title,
                address: createForm.value.address.trim() || null,
            });
            list.upsert(schedule);
            draftOpen.value = true;
            createOpen.value = false;
            createForm.value = { title: "", address: "" };
            await router.push({
                name: "schedule-editor",
                params: { id: String(schedule.id) },
            });
        } catch {
            createError.value = "Не удалось создать график. Попробуйте ещё раз.";
        } finally {
            creating.value = false;
        }
    }

    async function ensureLoaded(): Promise<void> {
        try {
            await list.refresh();
        } catch {
            // error уже в store
        }
    }

    onMounted(ensureLoaded);
    watch(
        () => route.fullPath,
        () => {
            if (route.name === "schedules") {
                void ensureLoaded();
            }
        },
    );
</script>

<style lang="scss" scoped>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        margin-bottom: 24px;
        min-height: 60px;

        :deep(.page-header__subtitle) {
            margin-top: 4px;
        }

        @include mq-md {
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;

            :deep(.page-header__subtitle) {
                max-width: 540px;
            }
        }

        @include mq-sm {
            :deep(.page-header__subtitle) {
                font-size: 14px;
                line-height: 20px;
            }
        }
    }

    .filter-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;

        @include mq-sm {
            flex-wrap: wrap;

            :deep(.app-input-field--inline) {
                flex-basis: 100%;
            }
        }
    }

    .select-like {
        position: relative;
        display: flex;
        align-items: center;
        width: 160px;
        height: 46px;
        border-radius: 8px;
        background: var(--bg-surface);
        flex-shrink: 0;
        color: var(--text-primary);

        select {
            appearance: none;
            border: 0;
            background: transparent;
            padding: 12px 44px 12px 12px;
            width: 100%;
            height: 100%;
            cursor: pointer;
            color: var(--text-primary);
            outline-offset: 2px;
        }

        &__chevron {
            position: absolute;
            right: 12px;
            pointer-events: none;
        }

        @include mq-sm {
            width: 100%;
        }
    }

    .status-group {
        border-radius: 20px;
        background: var(--bg-surface);
        margin-bottom: 8px;

        &__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            height: 64px;
            border: 0;
            background: transparent;
            padding: 16px 24px;
            border-radius: 20px;
            text-align: left;

            &:hover {
                background: var(--bg-list-hover);
            }

            @include mq-sm {
                padding: 16px;
            }
        }

        &__title {
            display: flex;
            align-items: center;
            gap: 10px;

            @include mq-sm {
                :deep(.app-title) {
                    font-size: 18px;
                }
            }
        }

        &__toggle {
            display: inline-flex;
            padding: 4px;
            border-radius: 99px;
            background: var(--bg-muted);
        }
    }

    .schedule-cards {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 348px));
        gap: 8px;
        padding: 0 16px 16px;

        :deep(.empty-state) {
            grid-column: 1 / -1;
        }

        @include mq-lg {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        @include mq-sm {
            grid-template-columns: minmax(0, 1fr);
        }
    }
</style>
