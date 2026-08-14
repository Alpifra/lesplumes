<script setup lang="ts">
defineProps<{
    label: string;
    value: string | number;
    unit?: string;
    caption?: string;
}>();
</script>

<template>
    <div class="kpi-card card">
        <div class="kpi-card__label">{{ label }}</div>
        <div class="kpi-card__value">
            {{ value }}
            <span v-if="unit" class="kpi-card__unit">{{ unit }}</span>
        </div>
        <p v-if="caption" class="kpi-card__caption">{{ caption }}</p>
        <slot />
    </div>
</template>
