import { describe, it, expect, vi, beforeEach } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import { createRouter, createMemoryHistory } from 'vue-router';
import SignupView from '@/views/Auth/SignupView.vue';

const { register } = vi.hoisted(() => ({ register: vi.fn() }));

vi.mock('@/API/useAuth', () => ({ useRegister: register }));

const mountAt = async (url: string) => {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: [
            { path: '/inscription', name: 'Signup', component: SignupView },
            { path: '/connexion', name: 'Login', component: { template: '<div />' } },
            { path: '/', name: 'Home', component: { template: '<div />' } },
        ],
    });
    router.push(url);
    await router.isReady();

    return mount(SignupView, { global: { plugins: [router] } });
};

describe('SignupView', () => {
    beforeEach(() => register.mockReset());

    it('only explains the invitation without a link from the mail', async () => {
        const wrapper = await mountAt('/inscription');

        expect(wrapper.find('form').exists()).toBe(false);
        expect(wrapper.text()).toContain('uniquement sur invitation');
    });

    it('needs the token, not just the email', async () => {
        const wrapper = await mountAt('/inscription?email=jeanne%40lesplumes.test');

        expect(wrapper.find('form').exists()).toBe(false);
    });

    it('signs up with the invited email and its token', async () => {
        register.mockResolvedValue({ errors: { message: 'stop' } });
        const wrapper = await mountAt('/inscription?email=jeanne%40lesplumes.test&invitation=abc');

        const email = wrapper.find('input[type="email"]');
        expect((email.element as HTMLInputElement).value).toBe('jeanne@lesplumes.test');
        expect(email.attributes('readonly')).toBeDefined();

        const inputs = wrapper.findAll('input');
        await inputs[0].setValue('Jeanne');
        await inputs[1].setValue('Doe');
        await inputs[2].setValue('jeanne.plume');
        await inputs[4].setValue('password');
        await inputs[5].setValue('password');
        await wrapper.find('form').trigger('submit');
        await flushPromises();

        expect(register).toHaveBeenCalledWith(expect.objectContaining({
            email: 'jeanne@lesplumes.test',
            invitation: 'abc',
        }));
    });
});
