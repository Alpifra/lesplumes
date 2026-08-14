<script setup lang="ts">
defineProps<{
    variant?: 'primary' | 'outline' | 'ghost';
    size?: 'default' | 'small';
    disabled?: boolean;
}>();
</script>

<template>
    <button
        class="btn"
        :class="[`btn--${variant ?? 'primary'}`, size === 'small' ? 'btn--small' : '']"
        :disabled="disabled"
    >
        <slot />
    </button>
</template>
