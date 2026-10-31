import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import type { Round } from '@/API/useRound';
import type { User } from '@/API/useUser';
import SessionDetailView from '@/views/SessionDetailView.vue';
import { awaitedCount, storyStatus } from '@/utils/session';

const { fetchRound, joker } = vi.hoisted(() => ({
    fetchRound: vi.fn(),
    joker: vi.fn(),
}));

vi.mock('@/API/useRound', () => ({
    useRound: fetchRound,
    useJoker: joker,
    useDownloadRoundZip: vi.fn(),
}));

vi.mock('@/API/useStory', () => ({ useDownloadStory: vi.fn() }));

vi.mock('vue-router', async (importOriginal) => ({
    ...await importOriginal<typeof import('vue-router')>(),
    useRoute: () => ({ params: { id: '7' } }),
}));

const plume = (id: number, first: string): User => ({
    id,
    first_name: first,
    last_name: 'Plume',
    user_name: `${first.toLowerCase()}${id}`,
    email: `${first.toLowerCase()}@lesplumes.test`,
    created_at: '2026-01-01T00:00:00Z',
    updated_at: '2026-01-01T00:00:00Z',
});

const jeanne = plume(1, 'Jeanne');
const auguste = plume(2, 'Auguste');

const round = (overrides: Partial<Round> = {}): Round => ({
    id: 7,
    master: plume(3, 'Camille'),
    participants: [jeanne, auguste],
    stories: [],
    jokers: [],
    jokers_left: 2,
    word: 'Zinzolin',
    status: 'en-cours',
    start_at: '2026-10-01T00:00:00Z',
    end_at: null,
    created_at: '2026-10-01T00:00:00Z',
    ...overrides,
});

const mountView = async () => {
    const wrapper = mount(SessionDetailView, {
        global: { stubs: { AppSidebar: true, AppTopBar: true, UploadModal: true, RouterLink: true } },
    });
    await flushPromises();

    return wrapper;
};

const jokerButton = (wrapper: Awaited<ReturnType<typeof mountView>>) =>
    wrapper.findAll('button').find(button => button.text().startsWith('Poser un joker'));

beforeEach(() => {
    fetchRound.mockReset();
    joker.mockReset();
    localStorage.setItem('user', JSON.stringify(jeanne));
});

describe('joker helpers', () => {
    it('counts a joker as handed in, not as a text', () => {
        const withJoker = round({ jokers: [1] });

        expect(storyStatus(withJoker, 1)).toBe('joker');
        expect(storyStatus(withJoker, 2)).toBe('en-cours');
        expect(awaitedCount(withJoker)).toBe(1);
    });
});

describe('SessionDetailView joker', () => {
    it('plays a joker after confirmation and shows it on the plume row', async () => {
        fetchRound.mockResolvedValue(round());
        joker.mockResolvedValue({ headers: { status: 200 }, data: round({ jokers: [1], jokers_left: 1 }) });

        const wrapper = await mountView();

        expect(jokerButton(wrapper)?.text()).toContain('2 restants cette année');

        await jokerButton(wrapper)!.trigger('click');
        expect(joker).not.toHaveBeenCalled();

        await wrapper.findAll('button').find(button => button.text() === 'Poser le joker')!.trigger('click');
        await flushPromises();

        expect(joker).toHaveBeenCalledWith(7, false);
        expect(wrapper.text()).toContain('Joker posé');
        expect(wrapper.text()).toContain('— Joker —');
    });

    it('takes the joker back', async () => {
        fetchRound.mockResolvedValue(round({ jokers: [1], jokers_left: 1 }));
        joker.mockResolvedValue({ headers: { status: 200 }, data: round() });

        const wrapper = await mountView();

        await wrapper.findAll('button').find(button => button.text() === 'Reprendre mon joker')!.trigger('click');
        await flushPromises();

        expect(joker).toHaveBeenCalledWith(7, true);
        expect(jokerButton(wrapper)).toBeDefined();
    });

    it('offers no joker once the year has none left', async () => {
        fetchRound.mockResolvedValue(round({ jokers_left: 0 }));

        const wrapper = await mountView();

        expect(jokerButton(wrapper)?.attributes('disabled')).toBeDefined();
    });

    it('offers no joker to the master of the session', async () => {
        localStorage.setItem('user', JSON.stringify(plume(3, 'Camille')));
        fetchRound.mockResolvedValue(round());

        const wrapper = await mountView();

        expect(jokerButton(wrapper)).toBeUndefined();
    });
});
