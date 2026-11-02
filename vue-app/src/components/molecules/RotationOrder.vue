<script setup lang="ts">
import { ref, computed } from 'vue';
import type { User } from '@/API/useUser';

const props = defineProps<{
    /** The circle in rotation order, as the API sends it. */
    plumes: User[];
    selectorId: number | null;
    /** Let the plume in turn hand the word over to the next plume. */
    interactive?: boolean;
    /** Fold the circle behind its title, closed until the plume opens it. */
    collapsible?: boolean;
}>();

const emit = defineEmits<{ (e: 'hand-off', plume: User): void }>();

const picked = ref<number | null>(null);

const open = ref(!props.collapsible);

const pickedPlume = computed(() => props.plumes.find(p => p.id === picked.value) ?? null);

// The word only passes to the plume right after the one in turn, the first
// plume following the last.
const successor = computed(() => {
    const index = props.plumes.findIndex(p => p.id === props.selectorId);

    if (index === -1 || props.plumes.length < 2) return null;

    return props.plumes[(index + 1) % props.plumes.length];
});

const isSelectable = (plume: User) => !!props.interactive && plume.id === successor.value?.id;

const toggle = (plume: User) => {
    if (!isSelectable(plume)) return;
    picked.value = picked.value === plume.id ? null : plume.id;
};
</script>

<template>
    <div class="rotation">
        <button
            v-if="collapsible"
            class="rotation__header rotation__toggle"
            :aria-expanded="open"
            @click="open = !open"
        >
            <span class="rotation__title">Ordre de rotation</span>
            <svg class="rotation__chevron" :class="{ 'rotation__chevron--open': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
        </button>

        <div v-if="open" class="rotation__header" :class="{ 'rotation__header--folded': collapsible }">
            <span v-if="!collapsible" class="rotation__title">Ordre de rotation</span>
            <span class="rotation__hint">
                {{ interactive && successor
                    ? `Cliquez sur ${successor.first_name} pour lui laisser la main.`
                    : 'Le choix du mot change de plume à chaque session, par ordre alphabétique des prénoms — sauf si elle laisse la main à la suivante.' }}
            </span>
        </div>

        <div v-if="open" class="rotation__grid">
            <component
                :is="isSelectable(plume) ? 'button' : 'div'"
                v-for="(plume, index) in plumes"
                :key="plume.id"
                class="rotation__plume"
                :class="{
                    'rotation__plume--turn': plume.id === selectorId,
                    'rotation__plume--picked': picked === plume.id,
                    'rotation__plume--selectable': isSelectable(plume),
                }"
                @click="toggle(plume)"
            >
                <span class="rotation__id">#{{ index + 1 }}</span>
                <span class="rotation__name">{{ plume.first_name }}<br>{{ plume.last_name }}</span>
                <span v-if="plume.id === selectorId" class="rotation__tag">à son tour</span>
                <span v-else-if="picked === plume.id" class="rotation__tag">lui laisser la main</span>
            </component>
        </div>

        <div v-if="open && pickedPlume" class="rotation__confirm">
            <span class="rotation__confirm-text">
                Laisser la main à <strong>{{ pickedPlume.first_name }} {{ pickedPlume.last_name }}</strong> ?
            </span>
            <button class="btn btn--ghost btn--small" @click="picked = null">Annuler</button>
            <button class="btn btn--primary btn--small" @click="emit('hand-off', pickedPlume)">Confirmer</button>
        </div>
    </div>
</template>
