import LoginView from "@/views/Auth/LoginView.vue";
import ResetPasswordVue from "@/views/Auth/ResetPasswordVue.vue";
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
        component: LoginView,
    },
    {
        path: "/mot-de-passe-oublie",
        name: "Reset Password",
        component: ResetPasswordVue,
    },
]

// list of all routes
const allRoutes: RouteRecordRaw[] = [
    ...navRoutes,
    ...loginRoutes,
    {
        path: "/profile",
        name: "Profil",
        component: HomeView,
    }
];

export { navRoutes, loginRoutes, allRoutes };