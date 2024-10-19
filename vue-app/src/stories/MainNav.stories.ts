import type { Meta } from "@storybook/vue3";
import { vueRouter } from "storybook-vue3-router";
import { navRoutes } from "@/router/routes";
import MainNav from "@/components/nav/MainNav.vue";
import NavAccount from "@/components/nav/NavAccount.vue";
import "../assets/scss/main.scss";

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories
const meta = {
    title: "Design System/Navigation",
    component: MainNav,
    tags: ["autodocs"],
} satisfies Meta<typeof MainNav>;

export default meta;
/*
 *👇 Render functions are a framework specific feature to allow you control on how the component renders.
 * See https://storybook.js.org/docs/api/csf
 * to learn how to use render functions.
 */
export const Navigation = () => ({
    components: { MainNav },
    template: '<main-nav />',
});

export const Account = () => ({
    components: { NavAccount },
    template: '<nav-account />'
})

Navigation.decorators = [vueRouter(navRoutes)];