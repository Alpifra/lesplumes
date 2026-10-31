<script setup lang="ts">
import { useMobileNav } from '@/composables/useMobileNav';

defineProps<{
    title: string;
    subtitle?: string;
}>();

const { toggle } = useMobileNav();
</script>

<template>
    <div class="topbar">
        <div class="topbar__heading">
            <h1 class="topbar__title">{{ title }}</h1>
            <span v-if="subtitle" class="topbar__subtitle">{{ subtitle }}</span>
        </div>
        <div class="topbar__actions">
            <slot name="actions" />
            <button class="topbar__burger" aria-label="Menu" @click="toggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#363062" stroke-width="1.8" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>
</template>
