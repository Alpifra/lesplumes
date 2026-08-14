<script setup lang="ts">
import { ref, computed } from 'vue';
import type { User } from '@/API/useUser';

const props = defineProps<{
    plumes: User[];
    selectorId: number | null;
    /** Let the plume in turn pick someone to hand the word over to. */
    interactive?: boolean;
}>();

const emit = defineEmits<{ (e: 'hand-off', plume: User): void }>();

const picked = ref<number | null>(null);

const pickedPlume = computed(() => props.plumes.find(p => p.id === picked.value) ?? null);

const isSelectable = (plume: User) => !!props.interactive && plume.id !== props.selectorId;

const toggle = (plume: User) => {
    if (!isSelectable(plume)) return;
    picked.value = picked.value === plume.id ? null : plume.id;
};
</script>

<template>
    <div class="rotation">
        <div class="rotation__header">
            <span class="rotation__title">Ordre de rotation</span>
            <span class="rotation__hint">
                {{ interactive
                    ? 'Cliquez sur une plume pour lui laisser la main.'
                    : "Le choix du mot change de plume à chaque session, par ordre d'identifiant — sauf si elle laisse la main." }}
            </span>
        </div>

        <div class="rotation__grid">
            <component
                :is="isSelectable(plume) ? 'button' : 'div'"
                v-for="plume in plumes"
                :key="plume.id"
                class="rotation__plume"
                :class="{
                    'rotation__plume--turn': plume.id === selectorId,
                    'rotation__plume--picked': picked === plume.id,
                    'rotation__plume--selectable': isSelectable(plume),
                }"
                @click="toggle(plume)"
            >
                <span class="rotation__id">#{{ plume.id }}</span>
                <span class="rotation__name">{{ plume.first_name }}<br>{{ plume.last_name }}</span>
                <span v-if="plume.id === selectorId" class="rotation__tag">à son tour</span>
                <span v-else-if="picked === plume.id" class="rotation__tag">lui laisser la main</span>
            </component>
        </div>

        <div v-if="pickedPlume" class="rotation__confirm">
            <span class="rotation__confirm-text">
                Laisser la main à <strong>{{ pickedPlume.first_name }} {{ pickedPlume.last_name }}</strong> ?
            </span>
            <button class="btn btn--ghost btn--small" @click="picked = null">Annuler</button>
            <button class="btn btn--primary btn--small" @click="emit('hand-off', pickedPlume)">Confirmer</button>
        </div>
    </div>
</template>
