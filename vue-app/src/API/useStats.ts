import { useFetch, METHODS } from "./useApi";

export interface Stats {
    avg_writing_days: number,
    /** Jokers the plume may still play this calendar year. */
    jokers_left: number,
    jokers_per_year: number,
}

/**
 * Aggregate stats for the authenticated user, computed server-side over
 * every round (not just the current page of the paginated lists).
 */
export async function useStats(): Promise<Stats> {
    const response = await useFetch('/stats', METHODS.GET);

    return (response.data as Stats) ?? { avg_writing_days: 0, jokers_left: 0, jokers_per_year: 0 };
}
