import LoginView from "@/views/Auth/LoginView.vue";
import SignupView from "@/views/Auth/SignupView.vue";
import ResetPasswordVue from "@/views/Auth/ResetPasswordVue.vue";
import HomeView from "@/views/HomeView.vue";
import SessionsView from "@/views/SessionsView.vue";
import SessionDetailView from "@/views/SessionDetailView.vue";
import PlumesView from "@/views/PlumesView.vue";
import type { RouteRecordRaw } from "vue-router";

const navRoutes: RouteRecordRaw[] = [
    {
        path: "/",
        name: "Home",
        component: HomeView,
    },
    {
        path: "/sessions",
        name: "Session",
        component: SessionsView,
    },
    {
        path: "/sessions/:id",
        name: "SessionDetail",
        component: SessionDetailView,
    },
    {
        path: "/les-plumes",
        name: "Les Plumes",
        component: PlumesView,
    },
];

const loginRoutes: RouteRecordRaw[] = [
    {
        path: "/connexion",
        name: "Login",
        component: LoginView,
    },
    {
        path: "/inscription",
        name: "Signup",
        component: SignupView,
    },
    {
        path: "/mot-de-passe-oublie",
        name: "Reset Password",
        component: ResetPasswordVue,
    },
];

const allRoutes: RouteRecordRaw[] = [
    ...navRoutes,
    ...loginRoutes,
];

export { navRoutes, loginRoutes, allRoutes };
