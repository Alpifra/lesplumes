<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppSidebar from '@/components/organisms/AppSidebar.vue';
import AppTopBar from '@/components/organisms/AppTopBar.vue';
import { useRankings } from '@/API/useRankings';
import type { Rankings, RankingEntry } from '@/API/useRankings';
import { useStorageUser } from '@/API/useUser';
import { initials, avatarHue } from '@/utils/session';

type RankingKey = keyof Rankings;

const rankings = ref<Rankings>({ reactivity: [], blabla: [], participations: [], jokers: [] });
const loading = ref(true);
const selected = ref<RankingKey>('reactivity');

const me = useStorageUser();

const plural = (n: number, word: string) => `${n} ${word}${n > 1 ? 's' : ''}`;

// The podium wears a crown instead of its rank number.
const crowns = ['rainbow', 'silver', 'bronze'];
const medalNames = ['1re place', '2e place', '3e place'];

const number = (n: number) => n.toLocaleString('fr-FR');

const cards = computed<{
    key: RankingKey,
    title: string,
    hint: string,
    entries: RankingEntry[],
    value: (e: RankingEntry) => string,
    detail: (e: RankingEntry) => string,
    /** What two plumes must share to be tied; the value unless told otherwise. */
    score?: (e: RankingEntry) => string,
}[]>(() => [
    {
        key: 'reactivity',
        title: 'Réactivité',
        hint: 'délai moyen entre l\'ouverture de la session et le rendu du texte',
        entries: rankings.value.reactivity,
        value: (e: RankingEntry) => `${number(e.value)} j`,
        detail: (e: RankingEntry) => plural(e.texts ?? 0, 'texte'),
    },
    {
        key: 'participations',
        title: 'Participation',
        hint: 'sessions jouées, comme cheffe ou plume ; un joker n\'en est pas une',
        entries: rankings.value.participations,
        value: (e: RankingEntry) => plural(e.value, 'session'),
        detail: (e: RankingEntry) => e.as_master ? `dont ${e.as_master} comme cheffe` : '',
    },
    {
        key: 'jokers',
        title: 'Joker',
        hint: 'jokers posés depuis toujours, le plus sobre en tête',
        entries: rankings.value.jokers,
        value: (e: RankingEntry) => plural(e.value, 'joker'),
        detail: (e: RankingEntry) => `sur ${plural(e.sessions ?? 0, 'session')}`,
        // Sessions break the tie server-side, so they are part of the score.
        score: (e: RankingEntry) => `${e.value}/${e.sessions}`,
    },
    {
        key: 'blabla',
        title: 'Blabla',
        hint: 'nombre moyen de caractères par texte',
        entries: rankings.value.blabla,
        value: (e: RankingEntry) => `${number(e.value)} car.`,
        detail: (e: RankingEntry) => `${plural(e.texts ?? 0, 'texte')} · ${number(e.total ?? 0)} au total`,
    },
]);

const card = computed(() => cards.value.find(c => c.key === selected.value)!);

// Competition ranking: tied plumes share a rank (and a crown), and the next
// one comes after all of them — 1, 1, 3.
const ranks = computed(() => {
    const score = card.value.score ?? ((e: RankingEntry) => String(e.value));
    return card.value.entries.map((entry, i, entries) =>
        entries.findIndex(other => score(other) === score(entry)) + 1);
});

onMounted(async () => {
    rankings.value = await useRankings();
    loading.value = false;
});
</script>

<template>
    <div class="page-shell">
        <AppSidebar />

        <div class="page-main">
            <AppTopBar title="Statistiques" subtitle="les classements du cercle, depuis toujours" />

            <div class="page-content">
                <!-- The rainbow of the first place's crown. -->
                <svg width="0" height="0" style="position:absolute" aria-hidden="true">
                    <defs>
                        <linearGradient id="crown-rainbow" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0" stop-color="#FF5E7E" />
                            <stop offset="0.25" stop-color="#FFB347" />
                            <stop offset="0.5" stop-color="#7BD88F" />
                            <stop offset="0.75" stop-color="#5BC0EB" />
                            <stop offset="1" stop-color="#A47CF3" />
                        </linearGradient>
                    </defs>
                </svg>

                <div class="rankings">
                    <div class="mode-toggle rankings__toggle" role="tablist" aria-label="Classement affiché">
                        <button
                            v-for="option in cards"
                            :key="option.key"
                            class="rankings__option"
                            :class="{ 'rankings__option--active': option.key === selected }"
                            role="tab"
                            :aria-selected="option.key === selected"
                            @click="selected = option.key"
                        >
                            {{ option.title }}
                        </button>
                    </div>

                    <div class="card ranking-card" role="tabpanel" :data-ranking="card.key">
                        <div class="ranking-card__header">
                            <h2 class="ranking-card__title">{{ card.title }}</h2>
                            <p class="ranking-card__hint">{{ card.hint }}</p>
                        </div>

                        <p v-if="loading" class="ranking-card__empty">Chargement…</p>
                        <p v-else-if="!card.entries.length" class="ranking-card__empty">Pas encore de classement.</p>

                        <ol v-else class="ranking-card__list">
                            <li
                                v-for="(entry, i) in card.entries"
                                :key="entry.plume.id"
                                class="ranking-card__row"
                                :class="{ 'ranking-card__row--me': entry.plume.id === me?.id}"
                            >
                                <span v-if="ranks[i] <= 3" class="ranking-card__rank" :title="medalNames[ranks[i] - 1]">
                                    <svg class="ranking-card__crown" :class="`ranking-card__crown--${crowns[ranks[i] - 1]}`" viewBox="0 0 24 24" role="img" :aria-label="medalNames[ranks[i] - 1]">
                                        <path d="M3 8l4.5 4L12 5l4.5 7L21 8l-2 11H5L3 8Z" />
                                        <rect x="5" y="20" width="14" height="2" rx="1" />
                                    </svg>
                                </span>
                                <span v-else class="ranking-card__rank">{{ ranks[i] }}</span>
                                <span class="user-avatar" :style="{width:'34px',height:'34px',fontSize:'13px',background:`hsl(${avatarHue(entry.plume.id)},60%,86%)`,color:`hsl(${avatarHue(entry.plume.id)},45%,30%)`}">{{ initials(`${entry.plume.first_name} ${entry.plume.last_name}`) }}</span>
                                <span class="ranking-card__name">
                                    {{ entry.plume.first_name }} {{ entry.plume.last_name }}
                                    <span v-if="card.detail(entry)" class="ranking-card__detail">{{ card.detail(entry) }}</span>
                                </span>
                                <span class="ranking-card__value">{{ card.value(entry) }}</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
