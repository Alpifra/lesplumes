import type { PillStatus } from '@/components/atoms/StatusPill.vue';
import type { Round } from '@/API/useRound';
import type { Story } from '@/API/useStory';

export const formatDate = (d?: string | null): string =>
    d
        ? new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
        : '—';

export const initials = (name: string): string =>
    (name || '')
        .trim()
        .split(/\s+/)
        .map(part => part[0] ?? '')
        .slice(0, 2)
        .join('')
        .toUpperCase();

export const avatarHue = (index: number): number => (index * 67) % 360;

/** The story a given user deposited on a round, if any. */
export const storyFor = (round: Round, userId: number): Story | undefined =>
    (round.stories ?? []).find(story => story.writer?.id === userId);

/**
 * The contribution status of a user on a round:
 * "rendu" once a PDF is attached, "en-cours" while the round is open,
 * "retard" if the round is finished without a deposit.
 */
export const storyStatus = (round: Round, userId: number): PillStatus => {
    if (storyFor(round, userId)?.media) return 'rendu';
    return round.status === 'termine' ? 'retard' : 'en-cours';
};

/**
 * Deposit date of a user on a round: the date the PDF was attached.
 * A story without media has been started, not deposited — hence `null`.
 */
export const depositDate = (round: Round, userId: number): string | null => {
    const story = storyFor(round, userId);

    return story?.media ? story.updated_at : null;
};

/** Number of participants who have deposited a PDF. */
export const renduCount = (round: Round): number =>
    (round.stories ?? []).filter(story => story.media).length;

/** Whole days left before a round's deadline (0 if past / undated). */
export const daysLeft = (round: Round): number => {
    if (!round.end_at) return 0;
    const diff = new Date(round.end_at).getTime() - Date.now();
    return diff > 0 ? Math.ceil(diff / (1000 * 60 * 60 * 24)) : 0;
};
