<script setup lang="ts">
import { ref, computed } from 'vue';
import type { User } from '@/API/useUser';
import type { Round } from '@/API/useRound';
import { useCreateRound, useHandOff } from '@/API/useRound';
import { formatDate } from '@/utils/session';
import { useRandomWord } from '@/API/useWord';
import RotationOrder from '@/components/molecules/RotationOrder.vue';
import UserAvatar from '@/components/molecules/UserAvatar.vue';

// The input only exists on the "compose" step, so it is focused as it mounts.
const vFocus = { mounted: (el: HTMLInputElement) => el.focus() };

const props = defineProps<{
    /** The plume whose turn it is — the one reading this card. */
    selector: User;
    plumes: User[];
    previousRound: Round | null;
}>();

const emit = defineEmits<{ (e: 'launched', roundId: number): void }>();

type Step = 'idle' | 'compose' | 'launched';

const step = ref<Step>('idle');
const word = ref('');
const drawing = ref(false);
const handedTo = ref<User | null>(null);
const submitting = ref(false);
const error = ref('');
const createdRoundId = ref<number | null>(null);

const guests = computed(() => props.plumes.filter(plume => plume.id !== props.selector.id));

/** Fill the input with a word of the dictionary nobody has played yet. */
const draw = async () => {
    if (drawing.value) return;

    drawing.value = true;
    word.value = await useRandomWord(word.value.trim());
    drawing.value = false;
};

const launch = async () => {
    const chosen = word.value.trim();
    if (!chosen || submitting.value) return;

    submitting.value = true;
    error.value = '';

    // An open-ended session: the plumes hand in their text when they wish.
    const response = await useCreateRound({
        word: chosen,
        master: props.selector.id,
        participants: guests.value.map(plume => plume.id),
        start_at: new Date().toISOString(),
        end_at: null,
    });

    submitting.value = false;

    if (response.errors) {
        error.value = response.errors.message || "La session n'a pas pu être ouverte.";
        return;
    }

    createdRoundId.value = (response.data as Round | null)?.id ?? null;
    step.value = 'launched';
};

const handOff = async (plume: User) => {
    if (submitting.value) return;

    submitting.value = true;
    error.value = '';

    const response = await useHandOff(plume.id);

    submitting.value = false;

    if (response.errors) {
        error.value = response.errors.message || "La main n'a pas pu être passée.";
        return;
    }

    handedTo.value = plume;
};

const cancelCompose = () => {
    step.value = 'idle';
    error.value = '';
};
</script>

<template>
    <!-- The word now belongs to someone else -->
    <div v-if="handedTo" class="card new-session-card">
        <div class="eyebrow eyebrow--soft">
            <span class="eyebrow__dot" />
            <span class="eyebrow__label">Main passée</span>
        </div>

        <div class="new-session-card__plume">
            <UserAvatar :name="`${handedTo.first_name} ${handedTo.last_name}`" :size="56" :index="handedTo.id" />
            <div>
                <h2 class="new-session-card__title">{{ handedTo.first_name }} {{ handedTo.last_name }} choisit le mot</h2>
                <p class="new-session-card__lede">
                    Vous lui avez laissé la main. La session commencera dès qu'un mot sera déposé.
                </p>
            </div>
        </div>
    </div>

    <!-- The session is open -->
    <div v-else-if="step === 'launched'" class="card new-session-card">
        <div class="eyebrow">
            <span class="eyebrow__dot" />
            <span class="eyebrow__label">Session lancée</span>
        </div>

        <div class="new-session-card__hero">
            <div>
                <h2 class="new-session-card__word">{{ word.trim() }}</h2>
                <div class="new-session-card__meta">
                    <span>Ouverte le {{ formatDate(new Date().toISOString()) }}</span>
                    <span>·</span>
                    <span>{{ guests.length }} plumes convoquées</span>
                </div>
            </div>
            <button
                v-if="createdRoundId"
                class="btn btn--primary"
                @click="emit('launched', createdRoundId)"
            >
                Voir la session
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px;pointer-events:none"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </div>

        <div class="new-session-card__notice">
            <span class="new-session-card__notice-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;pointer-events:none"><path d="M4 12l5 5L20 6"/></svg>
            </span>
            <span>
                Les {{ guests.length }} plumes du cercle ont été prévenues. Elles rendent leur rédaction quand elles le souhaitent.
            </span>
        </div>
    </div>

    <!-- Picking the word -->
    <div v-else-if="step === 'compose'" class="card new-session-card">
        <div class="eyebrow">
            <span class="eyebrow__dot" />
            <span class="eyebrow__label">Nouvelle session</span>
        </div>

        <h2 class="new-session-card__title">Choix du mot</h2>
        <p class="new-session-card__lede">
            Un seul mot. Il sera dévoilé aux autres plumes au lancement de la session.
        </p>

        <div class="new-session-card__compose">
            <input
                v-model="word"
                v-focus
                class="new-session-card__input"
                maxlength="24"
                placeholder="Écrivez un mot…"
                @keyup.enter="launch"
            >
            <button class="btn btn--outline new-session-card__dice" :disabled="drawing" @click="draw">
                <svg viewBox="0 0 24 24" fill="none" stroke="#9255FD" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;pointer-events:none"><path d="M21 12a9 9 0 1 1-3-6.7M21 4v5h-5"/></svg>
                <span>{{ drawing ? 'Tirage…' : 'Mot au hasard' }}</span>
            </button>
        </div>

        <div class="new-session-card__counter">
            {{ word.trim() ? `« ${word.trim()} » · ${word.trim().length} lettres` : '24 caractères maximum' }}
        </div>

        <p v-if="error" class="new-session-card__error">{{ error }}</p>

        <div class="new-session-card__actions">
            <button class="btn btn--ghost" @click="cancelCompose">Annuler</button>
            <button class="btn btn--primary" :disabled="!word.trim() || submitting" @click="launch">
                {{ submitting ? 'Ouverture…' : 'Lancer la session' }}
            </button>
        </div>
    </div>

    <!-- The turn is ours -->
    <div v-else class="card new-session-card">
        <div class="new-session-card__header">
            <div class="eyebrow">
                <span class="eyebrow__dot" />
                <span class="eyebrow__label">Nouvelle session</span>
            </div>
            <span v-if="previousRound" class="new-session-card__previous">
                « {{ previousRound.word }} » clôturée le {{ formatDate(previousRound.end_at) }}
            </span>
        </div>

        <div class="new-session-card__hero">
            <div>
                <h2 class="new-session-card__headline">C'est à vous de choisir le mot</h2>
                <p class="new-session-card__lede">Choisissez un mot, ou laissez la main à une plume.</p>
            </div>
            <button class="btn btn--primary" @click="step = 'compose'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px;pointer-events:none"><path d="M12 5v14M5 12h14"/></svg>
                Lancer une nouvelle session
            </button>
        </div>

        <p v-if="error" class="new-session-card__error">{{ error }}</p>

        <RotationOrder :plumes="plumes" :selector-id="selector.id" interactive @hand-off="handOff" />
    </div>
</template>
