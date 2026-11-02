import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises, shallowMount } from '@vue/test-utils';
import type { Rankings } from '@/API/useRankings';
import StatsView from '@/views/StatsView.vue';

const { rankings } = vi.hoisted(() => ({ rankings: vi.fn() }));

vi.mock('@/API/useRankings', () => ({ useRankings: rankings }));

const plume = (id: number, first_name: string) => ({ id, first_name, last_name: 'Plume' });

const data: Rankings = {
    reactivity: [
        { plume: plume(2, 'Auguste'), value: 1.5, texts: 4 },
        { plume: plume(1, 'Jeanne'), value: 6, texts: 1 },
    ],
    blabla: [{ plume: plume(1, 'Jeanne'), value: 12345, total: 24690, texts: 2 }],
    participations: [],
    jokers: [{ plume: plume(2, 'Auguste'), value: 2, sessions: 5 }],
};

describe('StatsView', () => {
    beforeEach(() => {
        rankings.mockResolvedValue(data);
        localStorage.setItem('user', JSON.stringify(plume(1, 'Jeanne')));
    });

    it('lists each ranking best first, with its value', async () => {
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        const rows = wrapper.findAll('[data-ranking="reactivity"] .ranking-card__row');
        expect(rows.map(r => r.find('.ranking-card__name').text())).toEqual([
            expect.stringContaining('Auguste'),
            expect.stringContaining('Jeanne'),
        ]);
        expect(rows[0].find('.ranking-card__value').text()).toBe('1,5 j');
        expect(rows[0].find('.ranking-card__detail').text()).toBe('4 textes');

    });

    it('shows one ranking at a time, picked from the toggle', async () => {
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        expect(wrapper.findAll('.ranking-card')).toHaveLength(1);
        expect(wrapper.find('[data-ranking="blabla"]').exists()).toBe(false);

        const options = wrapper.findAll('.rankings__option');
        expect(options.map(o => o.text())).toEqual(['Réactivité', 'Participations', 'Jokers', 'Blabla']);

        await options[3].trigger('click');

        expect(options[3].attributes('aria-selected')).toBe('true');
        const blabla = wrapper.find('[data-ranking="blabla"] .ranking-card__row');
        expect(blabla.find('.ranking-card__value').text()).toMatch(/^12\s345 car\.$/);
    });

    it('crowns the podium in rainbow, silver and bronze', async () => {
        rankings.mockResolvedValue({
            ...data,
            reactivity: [1, 2, 3, 4].map(id => ({ plume: plume(id, `Plume${id}`), value: id, texts: 1 })),
        });
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        const ranks = wrapper.findAll('.ranking-card__rank');
        expect(ranks.slice(0, 3).map(r => r.find('.ranking-card__crown').classes()))
            .toEqual([
                expect.arrayContaining(['ranking-card__crown--rainbow']),
                expect.arrayContaining(['ranking-card__crown--silver']),
                expect.arrayContaining(['ranking-card__crown--bronze']),
            ]);
        expect(ranks[3].find('svg').exists()).toBe(false);
        expect(ranks[3].text()).toBe('4');
    });

        it('counts the sessions played, as master or plume', async () => {
        rankings.mockResolvedValue({
            ...data,
            participations: [
                { plume: plume(1, 'Jeanne'), value: 7, as_master: 2 },
                { plume: plume(2, 'Auguste'), value: 1, as_master: 0 },
            ],
        });
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        await wrapper.findAll('.rankings__option')[1].trigger('click');

        const rows = wrapper.findAll('[data-ranking="participations"] .ranking-card__row');
        expect(rows[0].find('.ranking-card__value').text()).toBe('7 sessions');
        expect(rows[0].find('.ranking-card__detail').text()).toBe('dont 2 comme cheffe');
        expect(rows[1].find('.ranking-card__value').text()).toBe('1 session');
        expect(rows[1].find('.ranking-card__detail').exists()).toBe(false);
    });

        it('ranks the plumes on the jokers they played', async () => {
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        await wrapper.findAll('.rankings__option')[2].trigger('click');

        const row = wrapper.find('[data-ranking="jokers"] .ranking-card__row');
        expect(row.find('.ranking-card__value').text()).toBe('2 jokers');
        expect(row.find('.ranking-card__detail').text()).toBe('sur 5 sessions');
    });

    it('crowns every tied plume, the next one coming after them', async () => {
        rankings.mockResolvedValue({
            ...data,
            reactivity: [2, 2, 3, 4, 4].map((value, i) => ({ plume: plume(i + 1, `Plume${i + 1}`), value, texts: 1 })),
        });
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        const ranks = wrapper.findAll('.ranking-card__rank');
        expect(ranks.slice(0, 3).map(r => r.find('.ranking-card__crown').classes().at(-1))).toEqual([
            'ranking-card__crown--rainbow',
            'ranking-card__crown--rainbow',
            'ranking-card__crown--bronze',
        ]);
        expect(ranks.slice(3).map(r => r.text())).toEqual(['4', '4']);
    });

    it('ties jokers only on the same sessions too', async () => {
        rankings.mockResolvedValue({
            ...data,
            jokers: [
                { plume: plume(1, 'Jeanne'), value: 0, sessions: 5 },
                { plume: plume(2, 'Auguste'), value: 0, sessions: 5 },
                { plume: plume(3, 'Camille'), value: 0, sessions: 2 },
            ],
        });
        const wrapper = shallowMount(StatsView);
        await flushPromises();
        await wrapper.findAll('.rankings__option')[2].trigger('click');

        expect(wrapper.findAll('.ranking-card__crown').map(c => c.classes().at(-1))).toEqual([
            'ranking-card__crown--rainbow',
            'ranking-card__crown--rainbow',
            'ranking-card__crown--bronze',
        ]);
    });

    it('highlights the signed-in plume', async () => {
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        const mine = wrapper.findAll('.ranking-card__row--me');
        expect(mine).toHaveLength(1);
        expect(mine[0].text()).toContain('Jeanne');
    });

    it('says when a ranking is still empty', async () => {
        const wrapper = shallowMount(StatsView);
        await flushPromises();

        await wrapper.findAll('.rankings__option')[1].trigger('click');

        expect(wrapper.find('[data-ranking="participations"]').text()).toContain('Pas encore de classement.');
    });
});
