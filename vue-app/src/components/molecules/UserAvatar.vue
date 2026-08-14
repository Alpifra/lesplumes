<script setup lang="ts">
const props = defineProps<{
    name: string;
    size?: number;
    index?: number;
}>();

const initials = computed(() =>
    props.name.split(' ').map(p => p[0]).slice(0, 2).join('')
);

const hue = computed(() => ((props.index ?? 0) * 67) % 360);
</script>

<script lang="ts">
import { computed } from 'vue';
</script>

<template>
    <span
        class="user-avatar"
        :style="{
            width: `${size ?? 36}px`,
            height: `${size ?? 36}px`,
            fontSize: `${(size ?? 36) * 0.42}px`,
            background: `hsl(${hue}, 60%, 88%)`,
            color: `hsl(${hue}, 45%, 35%)`,
        }"
    >{{ initials }}</span>
</template>
