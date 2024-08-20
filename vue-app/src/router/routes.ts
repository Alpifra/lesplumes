import HomeView from "@/views/HomeView.vue";
import type { RouteRecordRaw } from "vue-router";

const navRoutes: RouteRecordRaw[] = [
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
        name: "Les Plumes",
        component: HomeView,
    },
];

const loginRoutes: RouteRecordRaw[] = [
    {
        path: "/connexion",
        name: "Login",
        component: HomeView,
    }
]

// list of all routes
const allRoutes: RouteRecordRaw[] = [
    ...navRoutes,
    ...loginRoutes,
];

export { navRoutes, loginRoutes, allRoutes };