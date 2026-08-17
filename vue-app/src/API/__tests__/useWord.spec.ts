import { describe, it, expect, vi, beforeEach } from 'vitest';
import { useRandomWord } from '@/API/useWord';
import { RANDOM_WORDS } from '@/utils/session';

const { fetchApi } = vi.hoisted(() => ({ fetchApi: vi.fn() }));

vi.mock('@/API/useApi', () => ({
    useFetch: fetchApi,
    METHODS: { GET: 'GET', POST: 'POST', PATCH: 'PATCH', DELETE: 'DELETE' },
}));

beforeEach(() => fetchApi.mockReset());

describe('useRandomWord', () => {
    it('capitalizes the word the dictionary hands back', async () => {
        fetchApi.mockResolvedValue({ headers: { status: 200 }, data: { word: 'zinzolin' } });

        await expect(useRandomWord()).resolves.toBe('Zinzolin');
    });

    it('passes the word already on screen so the draw changes it', async () => {
        fetchApi.mockResolvedValue({ headers: { status: 200 }, data: { word: 'chafouin' } });

        await useRandomWord('Amphigouri');

        expect(fetchApi).toHaveBeenCalledWith('/words/random?exclude=Amphigouri', 'GET');
    });

    it('falls back on the built-in pool rather than leaving the button dead', async () => {
        fetchApi.mockResolvedValue({
            headers: { status: 503 },
            data: null,
            errors: { message: 'Le dictionnaire est vide' },
        });

        expect(RANDOM_WORDS).toContain(await useRandomWord());
    });
});
