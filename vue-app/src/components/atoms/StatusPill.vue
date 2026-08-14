<script setup lang="ts">
export type PillStatus = 'rendu' | 'en-cours' | 'retard' | 'termine';

const props = defineProps<{ status: PillStatus }>();

const config: Record<PillStatus, { cls: string; label: string }> = {
    'rendu':    { cls: 'pill--success', label: 'Rendu' },
    'en-cours': { cls: 'pill--warn',    label: 'En cours' },
    'retard':   { cls: 'pill--error',   label: 'En retard' },
    'termine':  { cls: 'pill--neutral', label: 'Terminée' },
};

const current = computed(() => config[props.status] ?? config['en-cours']);
</script>

<script lang="ts">
import { computed } from 'vue';
</script>

<template>
    <span class="pill" :class="current.cls">{{ current.label }}</span>
</template>
