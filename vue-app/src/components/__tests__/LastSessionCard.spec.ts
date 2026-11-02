import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import type { Round } from '@/API/useRound';
import type { Story } from '@/API/useStory';
import type { User } from '@/API/useUser';
import LastSessionCard from '@/components/organisms/LastSessionCard.vue';

const { downloadStory, downloadZip } = vi.hoisted(() => ({
    downloadStory: vi.fn(),
    downloadZip: vi.fn(),
}));

vi.mock('@/API/useStory', () => ({ useDownloadStory: downloadStory }));
vi.mock('@/API/useRound', () => ({ useDownloadRoundZip: downloadZip }));

const plume = (id: number, first: string): User => ({
    id,
    first_name: first,
    last_name: 'Plume',
    user_name: `${first.toLowerCase()}${id}`,
    email: `${first.toLowerCase()}@lesplumes.test`,
    created_at: '2026-01-01T00:00:00Z',
    updated_at: '2026-01-01T00:00:00Z',
});

const story = (id: number, writer: User, title: string | null, withMedia = true): Story => ({
    id,
    title,
    writer,
    media: withMedia
        ? { id, url: null, extension: 'pdf', mime_type: 'application/pdf', size: 1, created_at: '', updated_at: '' }
        : null,
    created_at: '2026-10-20T00:00:00Z',
    updated_at: '2026-10-20T00:00:00Z',
});

const round = (stories: Story[]): Round => ({
    id: 9,
    master: plume(3, 'Camille'),
    participants: [plume(1, 'Jeanne'), plume(2, 'Auguste')],
    stories,
    jokers: [],
    word: 'Zinzolin',
    status: 'termine',
    start_at: '2026-10-01T00:00:00Z',
    end_at: '2026-10-25T00:00:00Z',
    created_at: '2026-10-01T00:00:00Z',
});

const mountCard = (stories: Story[]) => mount(LastSessionCard, {
    props: { round: round(stories) },
    global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
});

const texts = [
    story(11, plume(1, 'Jeanne'), 'Le grenier'),
    story(12, plume(2, 'Auguste'), null),
    // Started but never handed in: nothing to read.
    story(13, plume(3, 'Camille'), 'Brouillon', false),
];

beforeEach(() => {
    downloadStory.mockReset().mockResolvedValue({ ok: true });
    downloadZip.mockReset().mockResolvedValue({ ok: true });
});

describe('LastSessionCard', () => {
    it('lists the texts handed in', () => {
        const wrapper = mountCard(texts);
        const rows = wrapper.findAll('.last-session-card__story');

        expect(rows).toHaveLength(2);
        expect(rows[1].text()).toContain('Auguste Plume');
        expect(rows[1].text()).toContain('« Sans titre »');
        expect(wrapper.text()).toContain('2 textes rendus');
    });

    it('downloads a text', async () => {
        const wrapper = mountCard(texts);

        await wrapper.find('.last-session-card__read').trigger('click');
        await flushPromises();

        expect(downloadStory).toHaveBeenCalledWith(9, 11, 'Le grenier.pdf');
    });

    it('downloads every text through the archive', async () => {
        const wrapper = mountCard(texts);

        await wrapper.find('.last-session-card__all').trigger('click');
        await flushPromises();

        expect(downloadZip).toHaveBeenCalledWith(9);
    });

    it('offers nothing to download when no text was handed in', () => {
        const wrapper = mountCard([]);

        expect(wrapper.find('.last-session-card__list').exists()).toBe(false);
        expect(wrapper.find('.last-session-card__all').exists()).toBe(false);
    });
});
