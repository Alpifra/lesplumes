<script setup lang="ts">
import { computed, ref } from 'vue';
import type { Round } from '@/API/useRound';
import { useDownloadRoundZip } from '@/API/useRound';
import type { Story } from '@/API/useStory';
import { useDownloadStory } from '@/API/useStory';
import { formatDate } from '@/utils/session';

// The session that closed last, brought forward on the dashboard so its
// texts are one click away.
const props = defineProps<{ round: Round }>();

const handedIn = computed<Story[]>(() => (props.round.stories ?? []).filter(story => story.media));

const downloading = ref(false);

const download = async (fetchFile: () => Promise<unknown>) => {
    if (downloading.value) return;

    downloading.value = true;
    await fetchFile();
    downloading.value = false;
};

const readStory = (story: Story) => download(() =>
    useDownloadStory(props.round.id, story.id, `${story.title || story.writer?.user_name || 'texte'}.pdf`)
);

const readAll = () => download(() => useDownloadRoundZip(props.round.id));
</script>

<template>
    <div class="card last-session-card">
        <div class="home-session-card__status-row">
            <span class="home-session-card__dot" />
            <span class="home-session-card__status-label">Dernière session terminée</span>
        </div>

        <div class="last-session-card__hero">
            <div>
                <h2 class="last-session-card__word">{{ round.word }}</h2>
                <div class="home-session-card__meta">
                    <span>Close le {{ formatDate(round.end_at) }}</span>
                    <span>·</span>
                    <span>{{ handedIn.length }} {{ handedIn.length > 1 ? 'textes rendus' : 'texte rendu' }}</span>
                </div>
            </div>
            <RouterLink :to="{ name: 'SessionDetail', params: { id: round.id } }" class="btn btn--outline btn--small">
                Voir la session
            </RouterLink>
        </div>

        <template v-if="handedIn.length">
            <ul class="last-session-card__list">
                <li v-for="story in handedIn" :key="story.id" class="last-session-card__story">
                    <span class="last-session-card__writer">{{ story.writer?.first_name }} {{ story.writer?.last_name }}</span>
                    <span class="last-session-card__title">{{ story.title ? `« ${story.title} »` : '« Sans titre »' }}</span>
                    <button class="btn btn--ghost btn--small last-session-card__read" :disabled="downloading" @click="readStory(story)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="width:14px;height:14px"><path d="M12 3v12M6 11l6 6 6-6M4 21h16"/></svg>
                        PDF
                    </button>
                </li>
            </ul>
            <button class="btn btn--primary btn--small last-session-card__all" :disabled="downloading" @click="readAll">
                Tout télécharger (.zip)
            </button>
        </template>
    </div>
</template>
