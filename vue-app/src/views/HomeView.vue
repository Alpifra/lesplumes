<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import type { Round } from '@/API/useRound';
import { useRounds } from '@/API/useRound';
import { useStats } from '@/API/useStats';
import type { Story } from '@/API/useStory';
import AppSidebar from '@/components/organisms/AppSidebar.vue';
import AppTopBar from '@/components/organisms/AppTopBar.vue';
import StatusPill from '@/components/atoms/StatusPill.vue';
import UploadModal from '@/components/organisms/UploadModal.vue';
import { useStorageUser } from '@/API/useUser';
import type { PaginationMeta } from '@/API/useApi';
import { formatDate, initials, avatarHue, storyFor, storyStatus, depositDate } from '@/utils/session';

const router = useRouter();
const currentUser = useStorageUser();

const rounds = ref<Round[]>([]);
const meta = ref<PaginationMeta | null>(null);
const showUploadModal = ref(false);

const currentRound = computed<Round | null>(() =>
    rounds.value.find(r => r.status === 'en-cours') ?? rounds.value[0] ?? null
);

const pastRounds = computed<Round[]>(() =>
    rounds.value.filter(r => r.id !== currentRound.value?.id)
);

const canDeposit = computed(() => {
    const round = currentRound.value;
    if (!round || round.status === 'termine' || !currentUser) return false;
    const isParticipant = (round.participants ?? []).some(p => p.id === currentUser.id);
    return isParticipant || round.master?.id === currentUser.id;
});

interface ParticipantRow {
    id: number;
    name: string;
    date: string | null;
    status: ReturnType<typeof storyStatus>;
    isCurrentUser: boolean;
    hasStory: boolean;
}

const participants = computed<ParticipantRow[]>(() => {
    const round = currentRound.value;
    if (!round) return [];
    return (round.participants ?? []).map(p => {
        const story = storyFor(round, p.id);
        return {
            id: p.id,
            name: `${p.first_name} ${p.last_name}`,
            date: depositDate(round, p.id),
            status: storyStatus(round, p.id),
            isCurrentUser: p.id === currentUser?.id,
            hasStory: !!story?.media,
        };
    });
});

// --- KPIs derived from the loaded rounds ---
const participationTotal = computed(() => meta.value?.total ?? rounds.value.length);

// Average writing delay of the current user, computed server-side over every
// round (see /stats) so it doesn't depend on the paginated rounds loaded here.
const avgWritingDays = ref(0);

const allStories = computed<Story[]>(() => rounds.value.flatMap(r => r.stories ?? []));

const monthsChart = computed(() => {
    const now = new Date();
    const buckets = Array.from({ length: 4 }, (_, i) => {
        const d = new Date(now.getFullYear(), now.getMonth() - (3 - i), 1);
        return { m: d.toLocaleDateString('fr-FR', { month: 'short' }), key: `${d.getFullYear()}-${d.getMonth()}`, v: 0 };
    });
    allStories.value.forEach(story => {
        if (!story.media) return;
        const d = new Date(story.updated_at);
        const bucket = buckets.find(b => b.key === `${d.getFullYear()}-${d.getMonth()}`);
        if (bucket) bucket.v += 1;
    });
    return buckets;
});

// Top of the y axis, rounded up to an even number of plumes so the three
// ticks below stay whole and distinct.
const chartMax = computed(() => {
    const peak = Math.max(...monthsChart.value.map(b => b.v));
    return Math.max(2, Math.ceil(peak / 2) * 2);
});

const chartTicks = computed(() => [chartMax.value, chartMax.value / 2, 0]);

const load = async () => {
    const response = await useRounds();
    rounds.value = response.data;
    meta.value = response.meta;

    const stats = await useStats();
    avgWritingDays.value = stats.avg_writing_days;
};

const onDeposited = () => load();

const goToSession = () => router.push({ name: 'Session' });
const goToCurrent = () => {
    if (currentRound.value) router.push({ name: 'SessionDetail', params: { id: currentRound.value.id } });
};
const goToDetail = (round: Round) => router.push({ name: 'SessionDetail', params: { id: round.id } });

onMounted(load);
</script>

<template>
    <div class="page-shell">
        <AppSidebar />

        <div class="page-main">
            <AppTopBar title="Accueil" :subtitle="currentUser ? `le carnet de ${currentUser.first_name}` : 'le carnet'" />

            <div class="home-content">
                <!-- Left column -->
                <div class="home-content__left">

                    <!-- Current session card -->
                    <div v-if="currentRound" class="card home-session-card">
                        <div class="home-session-card__header">
                            <div class="home-session-card__status-row">
                                <span class="home-session-card__dot" />
                                <span class="home-session-card__status-label">{{ currentRound.status === 'en-cours' ? 'Session en cours' : 'Dernière session' }}</span>
                            </div>
                        </div>

                        <div class="home-session-card__hero">
                            <div>
                                <h2 class="home-session-card__word">{{ currentRound.word }}</h2>
                                <svg class="home-session-card__flourish" viewBox="0 0 200 24" fill="none" style="pointer-events:none">
                                    <path d="M 4 14 Q 40 2 82 12 T 166 14 Q 196 14 198 6" stroke="#9255FD" stroke-width="2.2" stroke-linecap="round"/>
                                </svg>
                                <div class="home-session-card__meta">
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px;vertical-align:middle;margin-right:5px;pointer-events:none"><path d="M4 6h16v15H4z"/><path d="M4 10h16M9 3v4M15 3v4"/></svg>
                                        {{ formatDate(currentRound.start_at) }} → {{ formatDate(currentRound.end_at) }}
                                    </span>
                                    <span>·</span>
                                    <span>{{ currentRound.participants?.length ?? 0 }} plumes engagées</span>
                                </div>
                            </div>
                            <button v-if="canDeposit" class="btn btn--primary" @click="showUploadModal = true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px;pointer-events:none"><path d="M12 19V7M6 13l6-6 6 6M4 21h16"/></svg>
                                Déposer ma rédaction
                            </button>
                            <button v-else class="btn btn--outline" @click="goToCurrent">
                                Voir la session
                            </button>
                        </div>

                        <!-- Participants table -->
                        <div class="data-table">
                            <div class="data-table__head" style="grid-template-columns: 2fr 1.4fr 1.2fr;">
                                <span>Plume</span>
                                <span>Date de dépôt</span>
                                <span>Statut</span>
                            </div>
                            <div
                                v-for="(row, i) in participants"
                                :key="row.id"
                                class="data-table__row"
                                style="grid-template-columns: 2fr 1.4fr 1.2fr;"
                            >
                                <span class="data-table__user">
                                    <span class="user-avatar" :style="{ width:'28px', height:'28px', fontSize:'12px', background:`hsl(${avatarHue(i)},60%,88%)`, color:`hsl(${avatarHue(i)},45%,35%)` }">{{ initials(row.name) }}</span>
                                    <span style="font-weight:500">{{ row.name }}</span>
                                </span>
                                <span class="data-table__muted">{{ formatDate(row.date) }}</span>
                                <span><StatusPill :status="row.status" /></span>
                            </div>
                        </div>
                    </div>

                    <!-- Past sessions card -->
                    <div class="card home-past-card">
                        <div class="card-header">
                            <div>
                                <div class="card-header__eyebrow">Carnet des sessions</div>
                                <h3 class="card-header__title">Sessions passées</h3>
                            </div>
                            <RouterLink :to="{ name: 'Session' }" class="btn btn--outline btn--small">
                                Voir tout
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px;pointer-events:none"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </RouterLink>
                        </div>

                        <div class="data-table home-past-card__table">
                            <div class="data-table__head" style="grid-template-columns: 2fr 0.8fr 1.2fr 1.2fr 1fr;">
                                <span>Mot</span><span>Plumes</span><span>Début</span><span>Fin</span>
                                <span style="text-align:right">Action</span>
                            </div>
                            <div
                                v-for="round in pastRounds"
                                :key="round.id"
                                class="data-table__row"
                                style="grid-template-columns: 2fr 0.8fr 1.2fr 1.2fr 1fr;"
                            >
                                <span class="data-table__word">{{ round.word }}</span>
                                <span class="data-table__muted">{{ round.participants?.length ?? 0 }}</span>
                                <span class="data-table__muted">{{ formatDate(round.start_at) }}</span>
                                <span class="data-table__muted">{{ formatDate(round.end_at) }}</span>
                                <span style="text-align:right">
                                    <button class="btn btn--ghost btn--small" style="color:#9255FD;font-weight:700" @click="goToDetail(round)">Voir la session →</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right KPI column -->
                <div class="home-content__kpis">
                    <div class="card kpi-card">
                        <div class="kpi-card__label">Participation</div>
                        <div class="kpi-card__value">{{ participationTotal }}</div>
                        <p class="kpi-card__caption">sessions de rédaction</p>
                        <button class="btn btn--outline btn--small" style="width:100%;margin-top:18px" @click="goToSession">Voir les sessions</button>
                    </div>

                    <div class="card kpi-card" style="text-align:left">
                        <div class="kpi-card__label" style="padding-right:56px">Temps moyen de rédaction</div>
                        <div style="display:flex;align-items:baseline;gap:6px;margin-top:10px;justify-content:center">
                            <span class="kpi-card__value" style="margin-top:0">{{ avgWritingDays.toFixed(1) }}</span>
                            <span class="kpi-card__unit">jours</span>
                        </div>
                        <div class="kpi-bar">
                            <div class="kpi-bar__fill" :style="{ width: `${Math.min(100, (avgWritingDays / 20) * 100)}%` }" />
                        </div>
                        <div class="kpi-bar__labels"><span>0j</span><span>20j</span></div>
                    </div>

                    <div class="card kpi-chart">
                        <div class="kpi-card__label">Les 4 derniers mois</div>
                        <div class="kpi-chart__title">Plumes écrites</div>
                        <div class="kpi-chart__bars">
                            <div class="kpi-chart__y">
                                <span v-for="(t, i) in chartTicks" :key="i">{{ t }}</span>
                            </div>
                            <div class="kpi-chart__area">
                                <div
                                    v-for="(d, i) in monthsChart"
                                    :key="i"
                                    class="kpi-chart__bar"
                                    :style="{height:`${(d.v/chartMax)*100}%`, background: i===monthsChart.length-1 ? '#9255FD' : '#B388FE', boxShadow: i===monthsChart.length-1 ? '0 4px 10px rgba(146,85,253,0.35)' : 'none'}"
                                >
                                    <span class="kpi-chart__bar-label">{{ d.v }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="kpi-chart__months">
                            <span v-for="d in monthsChart" :key="d.key" style="text-transform:capitalize">{{ d.m }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <UploadModal
            v-if="showUploadModal && currentRound"
            :round-id="currentRound.id"
            :round-word="currentRound.word"
            @close="showUploadModal = false"
            @deposited="onDeposited"
        />
    </div>
</template>
