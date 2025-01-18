import { createRouter, createWebHistory } from "vue-router";
import { allRoutes, loginRoutes } from "./routes";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: allRoutes
});

router.beforeEach(to => {

    const loginRouteNames = loginRoutes.map(route => route.name);

    // redirect to login if not auth
    if (
        !localStorage.getItem('user') &&
        !loginRouteNames.includes(to.name)
    ) {
        return router.push({ name: 'Login' });
    // TODO: add expiration token condition
    // redirect to home if try to access login while auth
    } else if (
        localStorage.getItem('user') &&
        loginRouteNames.includes(to.name)
    ) {
        return router.push({ name: 'Accueil' });
    }
});

export default router;
