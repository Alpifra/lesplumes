import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: "/",
            name: "Accueil",
            component: HomeView,
        },
        {
            path: "/session",
            name: "Session",
            component: HomeView,
        },
        {
            path: "/les-plumes",
            name: "Les plumes",
            component: HomeView,
        },
    ],
});

export default router;
