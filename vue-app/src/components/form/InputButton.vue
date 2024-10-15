<script setup lang="ts">

const props = defineProps<{
    title: string,
    disabled: boolean,
    shape: Shapes,
    size: Sizes,
    name?: string,
    value?: string,
    onClick?: () => void
}>();

type Shapes = 'primary' | 'secondary';
type Sizes = 'l' | 'm' | 's';

</script>

<template>
    <div class="input input-button" :class="{ [shape]: true, ['size-' + size]: true }">
        <button @click="onClick" type="submit" :disabled="disabled">{{ title }}</button>
        <slot class="input-button-icon"></slot>
    </div>
</template>