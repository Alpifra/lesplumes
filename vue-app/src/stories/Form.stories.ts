import type { Meta, StoryObj } from "@storybook/vue3";
import Form from "@/components/form/Form.vue";
import InputText from "@/components/form/InputText.vue";
import InputLongText from "@/components/form/InputLongText.vue";
import InputButton from "@/components/form/InputButton.vue";
import "../assets/scss/main.scss";

// More on how to set up stories at: https://storybook.js.org/docs/writing-stories
const meta = {
    title: "Component/Form",
    component: Form,
    tags: ["autodocs"],
} satisfies Meta<typeof Form>;

export default meta;

type InputTextStory = StoryObj<typeof InputText>
type InputLongTextStory = StoryObj<typeof InputLongText>
type ButtonStory = StoryObj<typeof InputButton>

/*
 *👇 Render functions are a framework specific feature to allow you control on how the component renders.
 * See https://storybook.js.org/docs/api/csf
 * to learn how to use render functions.
 */
export const TextField: InputTextStory = {
    render: (args) => ({
        components: { InputText },
        setup() {
            return { args }
        },
        template: `<input-text v-bind="args" />`,
    }),
    argTypes: {
        name: { control: "text", description: "The field name attribute." },
        value: { control: "text", description: "The field value." },
        placeholder: { control: "text", description: "The field placeholder." },
        password: { control: "boolean", description: "Show/Hide the field value." },
        label: { control: "text", description: "The field label." },
    },
    args: {
        name: "",
        value: "Some random text content.",
        placeholder: "Enter your text",
        password: false,
        label: "Text field"
    }
};

export const LongTextField: InputLongTextStory = {
    render: (args) => ({
        components: { InputLongText },
        setup() {
            return { args };
        },
        template: `<input-long-text v-bind="args" />`,
    }),
    argTypes: {
        name: { control: "text", description: "The field name attribute." },
        value: { control: "text", description: "The field value." },
        label: { control: "text", description: "The field label." },
        max: { control: { type: "number", min: 0, step: 10 }, description: "Maximum length of the field." },
        rows: { control: { type: "number", min: 0 }, description: "Default height of the text area." },
    },
    args: {
        name: "",
        value: "Some random long text content.",
        label: "Long text field",
        max: 50,
        rows: 0,
    }
};

export const Button: ButtonStory = {
    render: (args) => ({
        components: { InputButton },
        setup() {
            return { args };
        },
        template: `<input-button v-bind="args" />`,
    }),
    argTypes: {
        title: { control: "text", description: "The content of the button." },
        disabled: { control: "boolean", description: "Activate/Disable button." },
        shape: { control: "select", options: ["primary", "secondary"] },
        size: { control: "select", options: ["l", "m", "s"] },
        name: { control: "text", description: "The button name attribute as a pair with the value." },
        value: { control: "text", description: "The button value." },
    },
    args: {
        title: "Button",
        disabled: false,
        shape: "primary",
        size: "m",
        name: "",
        value: "",
    }
};