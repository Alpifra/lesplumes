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
            <button class="topbar__bell" title="Notifications">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#363062" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9Z" />
                    <path d="M10 21a2 2 0 0 0 4 0" />
                </svg>
                <span class="topbar__bell-dot" />
            </button>
            <button class="topbar__burger" aria-label="Menu" @click="toggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#363062" stroke-width="1.8" stroke-linecap="round">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>
        </div>
    </div>
</template>
