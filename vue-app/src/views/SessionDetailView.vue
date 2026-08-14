<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import AppSidebar from '@/components/organisms/AppSidebar.vue';
import AppTopBar from '@/components/organisms/AppTopBar.vue';
import StatusPill from '@/components/atoms/StatusPill.vue';
import UploadModal from '@/components/organisms/UploadModal.vue';
import { useStorageUser } from '@/API/useUser';
import { useRound, useDownloadRoundZip } from '@/API/useRound';
import { useDownloadStory } from '@/API/useStory';
import type { Round } from '@/API/useRound';
import type { User } from '@/API/useUser';
import { formatDate, initials, avatarHue, storyFor, storyStatus, depositDate, renduCount, daysLeft } from '@/utils/session';

const route = useRoute();
const currentUser = useStorageUser();

const round = ref<Round | null>(null);
const loading = ref(true);
const showUploadModal = ref(false);

const isEnded = computed(() => round.value?.status === 'termine');

const hasDeposits = computed(() => !!round.value && renduCount(round.value) > 0);

const canDeposit = computed(() => {
    if (!round.value || isEnded.value || !currentUser) return false;
    const isParticipant = (round.value.participants ?? []).some(p => p.id === currentUser.id);
    return isParticipant || round.value.master?.id === currentUser.id;
});

interface ContributionRow {
    user: User;
    title: string | null;
    date: string | null;
    status: ReturnType<typeof storyStatus>;
    storyId: number | null;
    hasMedia: boolean;
}

const rows = computed<ContributionRow[]>(() => {
    if (!round.value) return [];
    return (round.value.participants ?? []).map(user => {
        const story = storyFor(round.value!, user.id);
        return {
            user,
            title: story?.title ?? null,
            date: depositDate(round.value!, user.id),
            status: storyStatus(round.value!, user.id),
            storyId: story?.id ?? null,
            hasMedia: !!story?.media,
        };
    });
});

const load = async () => {
    loading.value = true;
    round.value = await useRound(Number(route.params.id));
    loading.value = false;
};

const onDeposited = () => load();

const downloadPdf = (row: ContributionRow) => {
    if (!round.value || !row.storyId || !isEnded.value) return;
    const name = `${(row.title || row.user.user_name || 'texte')}.pdf`;
    useDownloadStory(round.value.id, row.storyId, name);
};

const downloadZip = () => {
    if (round.value && hasDeposits.value && isEnded.value) useDownloadRoundZip(round.value.id);
};

onMounted(load);
</script>

<template>
    <div class="page-shell">
        <AppSidebar />

        <div class="page-main">
            <AppTopBar
                :title="isEnded ? 'Session terminée' : 'Session en cours'"
                subtitle="carnet du mot"
            />

            <div class="page-content">
                <RouterLink :to="{ name: 'Session' }" class="btn btn--ghost btn--small back-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                    Retour aux sessions
                </RouterLink>

                <div v-if="loading" class="data-table__empty">Chargement…</div>

                <template v-else-if="round">
                    <!-- Hero card -->
                    <div class="card session-hero">
                        <div class="session-hero__left">
                            <div class="session-hero__badge-row">
                                <span class="sessions-table__number" style="font-size:13px">Session #{{ String(round.id).padStart(3, '0') }}</span>
                                <StatusPill :status="round.status" />
                            </div>
                            <p class="session-hero__eyebrow">Le mot de la semaine</p>
                            <h1 class="session-hero__word">{{ round.word }}</h1>
                            <svg class="session-hero__flourish" viewBox="0 0 200 24" fill="none">
                                <path d="M 4 14 Q 40 2 82 12 T 166 14 Q 196 14 198 6" stroke="#9255FD" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                            <div class="session-hero__stats">
                                <div><div class="session-hero__stat-label">Début</div><div class="session-hero__stat-value">{{ formatDate(round.start_at) }}</div></div>
                                <div><div class="session-hero__stat-label">Fin</div><div class="session-hero__stat-value">{{ formatDate(round.end_at) }}</div></div>
                                <div><div class="session-hero__stat-label">Plumes engagées</div><div class="session-hero__stat-value">{{ round.participants?.length ?? 0 }}</div></div>
                                <div><div class="session-hero__stat-label">Rendu</div><div class="session-hero__stat-value">{{ renduCount(round) }}/{{ round.participants?.length ?? 0 }}</div></div>
                            </div>
                        </div>

                        <div class="session-hero__right">
                            <template v-if="!isEnded">
                                <span class="session-hero__countdown-label">encore</span>
                                <span class="session-hero__countdown">{{ daysLeft(round) }}</span>
                                <span class="data-table__muted" style="font-size:14px">jours pour écrire</span>
                                <button
                                    v-if="canDeposit"
                                    class="btn btn--primary"
                                    style="margin-top:14px;width:100%"
                                    @click="showUploadModal = true"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px"><path d="M12 19V7M6 13l6-6 6 6M4 21h16"/></svg>
                                    Déposer ma plume
                                </button>
                            </template>
                            <template v-else>
                                <div class="session-hero__stamp">CLÔTURÉE</div>
                                <span class="session-hero__countdown" style="font-size:42px">{{ renduCount(round) }}/{{ round.participants?.length ?? 0 }}</span>
                                <span class="data-table__muted" style="font-size:13px;margin-top:4px">plumes rendues</span>
                            </template>
                        </div>
                    </div>

                    <!-- Contributions table -->
                    <div class="card table-scroll">
                        <div class="card-header" style="padding: 22px 24px 18px;">
                            <div>
                                <div class="card-header__eyebrow">Contributions</div>
                                <h3 class="card-header__title">Les plumes de cette session</h3>
                            </div>
                            <button
                                v-if="hasDeposits && isEnded"
                                class="btn btn--outline btn--small"
                                title="Télécharger tous les textes déposés"
                                @click="downloadZip"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M12 3v12M6 11l6 6 6-6M4 21h16"/></svg>
                                Télécharger l'archive .zip
                            </button>
                        </div>
                        <div class="data-table__head" style="grid-template-columns: 2fr 2.5fr 1fr 1fr 1.2fr; text-transform:uppercase; letter-spacing:.04em; font-size:12px">
                            <span>Plume</span><span>Titre du texte</span><span>Date de dépôt</span><span>Statut</span><span style="text-align:right">Action</span>
                        </div>
                        <div
                            v-for="(row, i) in rows"
                            :key="row.user.id"
                            class="data-table__row"
                            style="grid-template-columns: 2fr 2.5fr 1fr 1fr 1.2fr;"
                        >
                            <span class="data-table__user">
                                <span class="user-avatar" :style="{width:'36px',height:'36px',fontSize:'14px',background:`hsl(${avatarHue(i)},60%,88%)`,color:`hsl(${avatarHue(i)},45%,35%)`}">{{ initials(`${row.user.first_name} ${row.user.last_name}`) }}</span>
                                <span style="font-weight:700;color:#363062">{{ row.user.first_name }} {{ row.user.last_name }}</span>
                            </span>
                            <span class="session-detail__title" :class="{ 'data-table__muted': !row.title }">
                                {{ row.title ? `« ${row.title} »` : (row.status === 'rendu' ? '« Sans titre »' : '— En cours de rédaction —') }}
                            </span>
                            <span class="data-table__muted">{{ formatDate(row.date) }}</span>
                            <span><StatusPill :status="row.status" /></span>
                            <span style="text-align:right">
                                <button
                                    v-if="row.hasMedia && isEnded"
                                    class="btn btn--ghost btn--small"
                                    style="color:#9255FD;font-weight:700"
                                    @click="downloadPdf(row)"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M12 3v12M6 11l6 6 6-6M4 21h16"/></svg>
                                    PDF
                                </button>
                                <button
                                    v-else-if="row.hasMedia"
                                    class="btn btn--ghost btn--small"
                                    style="color:#89868D;font-weight:700;background:transparent"
                                    disabled
                                    title="Téléchargement disponible à la clôture de la session"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M12 3v12M6 11l6 6 6-6M4 21h16"/></svg>
                                    PDF
                                </button>
                                <span v-else class="data-table__muted" style="font-style:italic;font-size:13px">—</span>
                            </span>
                        </div>
                    </div>
                </template>

                <div v-else class="data-table__empty">
                    Cette session est introuvable ou ne vous est pas accessible.
                </div>
            </div>
        </div>

        <UploadModal
            v-if="showUploadModal && round"
            :round-id="round.id"
            :round-word="round.word"
            @close="showUploadModal = false"
            @deposited="onDeposited"
        />
    </div>
</template>
