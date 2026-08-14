<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppSidebar from '@/components/organisms/AppSidebar.vue';
import AppTopBar from '@/components/organisms/AppTopBar.vue';
import StatusPill from '@/components/atoms/StatusPill.vue';
import { useRounds } from '@/API/useRound';
import type { Round, RoundStatus } from '@/API/useRound';
import type { PaginationMeta } from '@/API/useApi';
import { formatDate, renduCount } from '@/utils/session';

const router = useRouter();

const rounds = ref<Round[]>([]);
const meta = ref<PaginationMeta | null>(null);
const loading = ref(false);

const search = ref('');
const status = ref<RoundStatus | ''>('');
const dateFrom = ref('');
const dateTo = ref('');
const page = ref(1);

const showStatusMenu = ref(false);
const showDateFilter = ref(false);

const statusLabels: Record<RoundStatus | '', string> = {
    '': 'tous',
    'en-cours': 'en cours',
    'termine': 'terminées',
};

const load = async () => {
    loading.value = true;
    const response = await useRounds({
        search: search.value,
        status: status.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        page: page.value,
    });
    rounds.value = response.data;
    meta.value = response.meta;
    loading.value = false;
};

// Debounced reload when a filter changes; always reset to the first page.
let debounce: ReturnType<typeof setTimeout>;
watch([search, status, dateFrom, dateTo], () => {
    page.value = 1;
    clearTimeout(debounce);
    debounce = setTimeout(load, 300);
});
watch(page, load);

const selectStatus = (value: RoundStatus | '') => {
    status.value = value;
    showStatusMenu.value = false;
};

const pad = (id: number) => String(id).padStart(3, '0');
const goToDetail = (round: Round) => router.push({ name: 'SessionDetail', params: { id: round.id } });

onMounted(load);
</script>

<template>
    <div class="page-shell">
        <AppSidebar />

        <div class="page-main">
            <AppTopBar title="Sessions" subtitle="le carnet des mots" />

            <div class="page-content">
                <!-- Filter bar -->
                <div class="card filter-bar">
                    <div class="filter-bar__search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" class="filter-bar__icon"><path d="M11 4a7 7 0 1 1 0 14 7 7 0 0 1 0-14ZM21 21l-5-5"/></svg>
                        <input v-model="search" class="field" placeholder="Chercher un mot… (ex: tarabiscoté)" style="padding-left:44px" />
                    </div>

                    <button class="btn btn--outline" :class="{ 'btn--active': showDateFilter }" @click="showDateFilter = !showDateFilter">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px"><path d="M4 6h16v15H4z"/><path d="M4 10h16M9 3v4M15 3v4"/></svg>
                        Filtrer par date
                    </button>

                    <div class="filter-bar__dropdown-wrap">
                        <button class="btn btn--outline" @click="showStatusMenu = !showStatusMenu">
                            Statut : {{ statusLabels[status] }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div v-if="showStatusMenu" class="filter-bar__menu">
                            <button class="filter-bar__menu-item" @click="selectStatus('')">Tous</button>
                            <button class="filter-bar__menu-item" @click="selectStatus('en-cours')">En cours</button>
                            <button class="filter-bar__menu-item" @click="selectStatus('termine')">Terminées</button>
                        </div>
                    </div>
                </div>

                <!-- Date range (revealed) -->
                <div v-if="showDateFilter" class="card filter-bar filter-bar--dates">
                    <label class="field-label">Du<input v-model="dateFrom" class="field" type="date" /></label>
                    <label class="field-label">Au<input v-model="dateTo" class="field" type="date" /></label>
                    <button class="btn btn--ghost btn--small" @click="dateFrom = ''; dateTo = ''">Réinitialiser</button>
                </div>

                <!-- Sessions table -->
                <div class="card table-scroll">
                    <div class="data-table__head" style="grid-template-columns: 80px 2fr 1fr 1fr 1.3fr 1.2fr 1fr 1fr; text-transform:uppercase; letter-spacing:.04em; font-size:12.5px">
                        <span>N°</span><span>Mot</span><span>Plumes</span><span>Rendu</span>
                        <span>Début</span><span>Fin</span><span>Statut</span>
                        <span style="text-align:right">Action</span>
                    </div>

                    <div v-if="loading" class="data-table__empty">Chargement…</div>
                    <div v-else-if="!rounds.length" class="data-table__empty">Aucune session ne correspond à ces critères.</div>

                    <div
                        v-for="(round, i) in rounds"
                        :key="round.id"
                        class="data-table__row"
                        :style="{
                            gridTemplateColumns: '80px 2fr 1fr 1fr 1.3fr 1.2fr 1fr 1fr',
                            background: i % 2 === 1 ? 'rgba(146,85,253,0.02)' : 'transparent',
                        }"
                    >
                        <span class="sessions-table__number">#{{ pad(round.id) }}</span>
                        <span class="data-table__word">{{ round.word }}</span>
                        <span class="data-table__muted">{{ round.participants?.length ?? 0 }}</span>
                        <span style="font-weight:700">{{ renduCount(round) }}/{{ round.participants?.length ?? 0 }}</span>
                        <span class="data-table__muted">{{ formatDate(round.start_at) }}</span>
                        <span class="data-table__muted">{{ formatDate(round.end_at) }}</span>
                        <span><StatusPill :status="round.status" /></span>
                        <span style="text-align:right">
                            <button class="btn btn--ghost btn--small" style="color:#9255FD;font-weight:700" @click="goToDetail(round)">Voir →</button>
                        </span>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="meta && meta.last_page > 1" class="pagination">
                    <span class="data-table__muted">{{ rounds.length }} sessions sur {{ meta.total }}</span>
                    <div class="pagination__controls">
                        <button class="btn btn--outline btn--small" :disabled="page <= 1" @click="page = Math.max(1, page - 1)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                        </button>
                        <button
                            v-for="p in meta.last_page"
                            :key="p"
                            class="btn btn--small pagination__page"
                            :class="p === page ? 'btn--primary' : 'btn--outline'"
                            @click="page = p"
                        >{{ p }}</button>
                        <button class="btn btn--outline btn--small" :disabled="page >= meta.last_page" @click="page = Math.min(meta.last_page, page + 1)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
