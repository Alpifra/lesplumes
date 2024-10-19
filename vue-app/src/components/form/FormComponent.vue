<script setup lang="ts">

import { ref } from 'vue'

const props = defineProps<{
    onSubmit?: () => void
}>();

const valid = ref(false);
const onSubmit = (event: Event) => {
    event.preventDefault();
    if (props.onSubmit) props.onSubmit();
};



</script>

<template>
    <div class="form" @onSubmit="onSubmit">
        <slot></slot>
        <slot name="submit"></slot>
    </div>
</template>