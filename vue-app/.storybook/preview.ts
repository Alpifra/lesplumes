import type { Preview } from "@storybook/vue3";

const preview: Preview = {
    parameters: {
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },
        backgrounds: {
            default: "neutral",
            values: [
                { name: "Neutral", value: "#FFFF" },
                { name: "Background", value: "#F5F5F5" },
                { name: "Background-2", value: "#C4C8DC" },
            ],
        },
    },
};

export default preview;
