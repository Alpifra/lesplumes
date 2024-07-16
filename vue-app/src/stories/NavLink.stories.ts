import type { Meta, StoryObj } from "@storybook/vue3";
import NavLink from "../components/nav/NavLink.vue";
import "../assets/scss/main.scss";
import router from "@/router";

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories
const meta = {
    title: "Example/NavLink",
    component: NavLink,
    tags: ["autodocs"],
    argTypes: {
        title: {
            control: "select",
            options: router.getRoutes().map((route) => route.name),
        },
        targetUrl: { control: "text" },
        active: { control: "boolean", required: false },
    },
    args: {
        title: "Home",
        targetUrl: "/",
        active: false,
    },
} satisfies Meta<typeof NavLink>;

export default meta;
type Story = StoryObj<typeof meta>;
/*
 *👇 Render functions are a framework specific feature to allow you control on how the component renders.
 * See https://storybook.js.org/docs/api/csf
 * to learn how to use render functions.
 */
export const Active: Story = {
    args: {
        title: "Home",
        targetUrl: "/",
        active: true,
    },
};

export const Inactive: Story = {
    args: {
        title: "Home",
        targetUrl: "/",
        active: false,
    },
};
