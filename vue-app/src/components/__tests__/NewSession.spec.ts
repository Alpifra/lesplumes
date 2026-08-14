import { describe, it, expect, vi, beforeEach } from 'vitest';
import { mount } from '@vue/test-utils';
import type { User } from '@/API/useUser';
import NewSessionCard from '@/components/organisms/NewSessionCard.vue';
import WaitingSessionCard from '@/components/organisms/WaitingSessionCard.vue';
import RotationOrder from '@/components/molecules/RotationOrder.vue';

const { createRound, handOff } = vi.hoisted(() => ({
    createRound: vi.fn(),
    handOff: vi.fn(),
}));

vi.mock('@/API/useRound', () => ({
    useCreateRound: createRound,
    useHandOff: handOff,
}));

const plume = (id: number, first: string, last: string): User => ({
    id,
    first_name: first,
    last_name: last,
    user_name: `${first.toLowerCase()}${id}`,
    email: `${first.toLowerCase()}@lesplumes.test`,
    created_at: '2026-01-01T00:00:00Z',
    updated_at: '2026-01-01T00:00:00Z',
});

const circle = [
    plume(1, 'Jeanne', 'Doe'),
    plume(2, 'Auguste', 'Morel'),
    plume(3, 'Camille', 'Lefèvre'),
];

beforeEach(() => {
    createRound.mockReset();
    handOff.mockReset();
});

describe('RotationOrder', () => {
    it('marks the plume in turn and stays inert when not interactive', async () => {
        const wrapper = mount(RotationOrder, {
            props: { plumes: circle, selectorId: 2 },
        });

        const cards = wrapper.findAll('.rotation__plume');

        expect(cards).toHaveLength(3);
        expect(cards[1].classes()).toContain('rotation__plume--turn');
        expect(cards[1].text()).toContain('à son tour');
        expect(wrapper.findAll('button.rotation__plume')).toHaveLength(0);

        await cards[0].trigger('click');

        expect(wrapper.find('.rotation__confirm').exists()).toBe(false);
    });

    it('asks to confirm before handing the word over', async () => {
        const wrapper = mount(RotationOrder, {
            props: { plumes: circle, selectorId: 1, interactive: true },
        });

        // The plume in turn is never a hand-off target.
        expect(wrapper.findAll('button.rotation__plume')).toHaveLength(2);

        await wrapper.findAll('.rotation__plume')[2].trigger('click');

        expect(wrapper.find('.rotation__confirm').text()).toContain('Camille Lefèvre');
        expect(wrapper.emitted('hand-off')).toBeUndefined();

        await wrapper.find('.rotation__confirm .btn--primary').trigger('click');

        expect(wrapper.emitted('hand-off')?.[0]).toEqual([circle[2]]);
    });
});

describe('NewSessionCard', () => {
    const mountCard = () => mount(NewSessionCard, {
        props: { selector: circle[0], plumes: circle, previousRound: null },
    });

    it('opens a session with the chosen word and convenes the rest of the circle', async () => {
        createRound.mockResolvedValue({ headers: { status: 201 }, data: { id: 42 } });

        const wrapper = mountCard();

        expect(wrapper.text()).toContain("C'est à vous de choisir le mot");

        await wrapper.find('.new-session-card__hero .btn--primary').trigger('click');
        await wrapper.find('.new-session-card__input').setValue('Zinzolin');
        await wrapper.find('.new-session-card__actions .btn--primary').trigger('click');
        await Promise.resolve();
        await wrapper.vm.$nextTick();

        expect(createRound).toHaveBeenCalledWith(expect.objectContaining({
            word: 'Zinzolin',
            master: 1,
            participants: [2, 3],
            end_at: null,
        }));

        expect(wrapper.find('.new-session-card__word').text()).toBe('Zinzolin');

        await wrapper.find('.new-session-card__hero .btn--primary').trigger('click');

        expect(wrapper.emitted('launched')?.[0]).toEqual([42]);
    });

    it('fills the input with a random word without opening anything', async () => {
        const wrapper = mountCard();

        await wrapper.find('.new-session-card__hero .btn--primary').trigger('click');
        await wrapper.find('.new-session-card__dice').trigger('click');

        const input = wrapper.find<HTMLInputElement>('.new-session-card__input');

        expect(input.element.value.length).toBeGreaterThan(0);
        expect(createRound).not.toHaveBeenCalled();
    });

    it('reports a refused session instead of pretending it opened', async () => {
        createRound.mockResolvedValue({ headers: { status: 403 }, data: null, errors: { message: "Ce n'est pas votre tour." } });

        const wrapper = mountCard();

        await wrapper.find('.new-session-card__hero .btn--primary').trigger('click');
        await wrapper.find('.new-session-card__input').setValue('Chafouin');
        await wrapper.find('.new-session-card__actions .btn--primary').trigger('click');
        await Promise.resolve();
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.new-session-card__error').text()).toBe("Ce n'est pas votre tour.");
        expect(wrapper.find('.new-session-card__word').exists()).toBe(false);
    });

    it('hands the word over and says who holds it now', async () => {
        handOff.mockResolvedValue({ headers: { status: 200 }, data: { id: 3 } });

        const wrapper = mountCard();

        await wrapper.findAll('.rotation__plume')[2].trigger('click');
        await wrapper.find('.rotation__confirm .btn--primary').trigger('click');
        await Promise.resolve();
        await wrapper.vm.$nextTick();

        expect(handOff).toHaveBeenCalledWith(3);
        expect(wrapper.text()).toContain('Camille Lefèvre choisit le mot');
    });
});

describe('WaitingSessionCard', () => {
    it('names the plume the circle is waiting on', () => {
        const wrapper = mount(WaitingSessionCard, {
            props: { selector: circle[1], plumes: circle },
        });

        expect(wrapper.find('.new-session-card__headline').text()).toBe('Auguste Morel');
        expect(wrapper.text()).toContain('La session va commencer');

        // A watcher cannot take the turn from the plume who holds it.
        expect(wrapper.findAll('button.rotation__plume')).toHaveLength(0);
    });
});
