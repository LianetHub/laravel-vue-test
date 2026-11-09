import { createRouter, createWebHistory } from "vue-router";
import HomePage from "@/pages/HomePage.vue";
import NotFoundPage from "@/pages/NotFoundPage.vue";
import SchedulesListPage from "@/pages/SchedulesListPage.vue";
import ScheduleEditorPage from "@/pages/ScheduleEditorPage.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", name: "home", component: HomePage },
        { path: "/schedules", name: "schedules", component: SchedulesListPage },
        {
            path: "/schedules/:id",
            name: "schedule-editor",
            component: ScheduleEditorPage,
            props: true,
        },
        {
            path: "/:pathMatch(.*)*",
            name: "not-found",
            component: NotFoundPage,
        },
    ],
    linkActiveClass: "is-active",
    linkExactActiveClass: "is-active",
});

export default router;
