<script setup lang="ts">

import { ref } from 'vue';

const props = defineProps<{
    name: string,
    value?: string,
    placeholder?: string,
    label?: string,
    max?: number,
    rows?: number,
    onChange?: () => void
}>();

const hasError = ref(false);
const count = ref(props.value?.length ?? 0);
const handleChange = (event: Event) => {

    if (props.onChange) props.onChange();

    const textarea = event.target as HTMLTextAreaElement;
    count.value = textarea.value.length;

    if (props.max && count.value > props.max) {
        hasError.value = true;
    } else {
        hasError.value = false;
    }
}

</script>

<template>
    <div class="input input-longText" :class="{ 'error': hasError }">
        <div v-if="label">
            <label :for="name">{{ label }}</label>
        </div>
        <div>
            <textarea
                @input="handleChange"
                :rows="rows"
                :name="name"
                :id="name"
                :placeholder="placeholder"
            >{{ value }}</textarea>
        </div>
        <div v-if="max" class="input-longText-counter">
            <small>{{ count }}/{{ max }}</small>
        </div>
    </div>
</template>