import { useFetch, useDownload, usePaginatedFetch, METHODS } from "./useApi";
import { useXsrfToken } from "./useAuth";
import type { User } from "@/API/useUser";
import type { Story } from "./useStory";

export type RoundStatus = 'en-cours' | 'termine';

export interface Round {
    id: number,
    master: User,
    participants: User[],
    stories: Story[],
    word: string,
    status: RoundStatus,
    start_at: string | null,
    end_at: string | null,
    created_at: string,
}

export interface RoundFilters {
    search?: string,
    status?: RoundStatus | '',
    date_from?: string,
    date_to?: string,
    page?: number,
}

const routePrefix = '/rounds';

/**
 * The API nests related collections under a `data` key
 * (`participants: { data: [...] }`). Flatten them so views can work with
 * plain arrays.
 */
function normalizeRound(raw: any): Round {
    return {
        ...raw,
        participants: raw?.participants?.data ?? raw?.participants ?? [],
        stories: raw?.stories?.data ?? raw?.stories ?? [],
    } as Round;
}

export async function useRounds(filters: RoundFilters = {}) {
    const params = new URLSearchParams();

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            params.append(key, String(value));
        }
    });

    const query = params.toString();
    const response = await usePaginatedFetch<any>(routePrefix + (query ? `?${query}` : ''));

    return { ...response, data: response.data.map(normalizeRound) };
}

/**
 * A round the user takes no part in answers 403, and an unknown one 404:
 * both surface as `null` so the view can show an empty state.
 */
export async function useRound(id: number): Promise<Round | null> {
    const round = await useFetch(routePrefix + "/" + String(id), METHODS.GET);

    return round.data ? normalizeRound(round.data) : null;
}

/** Who holds the word for the session to come, and the circle behind her. */
export interface NextSession {
    selector: User | null,
    plumes: User[],
    previous_round: Round | null,
}

export async function useNextSession(): Promise<NextSession> {
    const response = await useFetch(routePrefix + '/next', METHODS.GET);
    const data = response.data as any;

    return {
        selector: data?.selector ?? null,
        plumes: data?.plumes?.data ?? data?.plumes ?? [],
        previous_round: data?.previous_round ? normalizeRound(data.previous_round) : null,
    };
}

export interface NewRound {
    word: string,
    master: number,
    participants: number[],
    start_at?: string | null,
    end_at?: string | null,
}

export async function useCreateRound(round: NewRound) {
    const token = await useXsrfToken();

    return useFetch(routePrefix, METHODS.POST, round, { 'X-XSRF-TOKEN': token ?? '' });
}

/** Let another plume pick the word of the next session in our place. */
export async function useHandOff(plumeId: number) {
    const token = await useXsrfToken();

    return useFetch(
        `${routePrefix}/hand-off`,
        METHODS.POST,
        { plume: plumeId },
        { 'X-XSRF-TOKEN': token ?? '' }
    );
}

export async function useInvitePlume(roundId: number, email: string) {
    const token = await useXsrfToken();

    return useFetch(
        `${routePrefix}/${roundId}/invite`,
        METHODS.POST,
        { email },
        { 'X-XSRF-TOKEN': token ?? '' }
    );
}

export function useDownloadRoundZip(roundId: number) {
    return useDownload(`${routePrefix}/${roundId}/download`, `session-${roundId}.zip`);
}
