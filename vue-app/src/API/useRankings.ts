import { useFetch, METHODS } from "./useApi";

export interface RankedPlume {
    id: number,
    first_name: string,
    last_name: string,
}

export interface RankingEntry {
    plume: RankedPlume,
    /** Days (reactivity), characters per text (blabla), sessions (participations) or jokers played. */
    value: number,
    texts?: number,
    /** Characters over every text (blabla only). */
    total?: number,
    /** Sessions the plume was convened to (jokers only). */
    sessions?: number,
    /** Sessions the plume led as master (participations only). */
    as_master?: number,
}

export interface Rankings {
    reactivity: RankingEntry[],
    participations: RankingEntry[],
    jokers: RankingEntry[],
    blabla: RankingEntry[],
}

/**
 * The rankings of the circle over its whole history, best first.
 */
export async function useRankings(): Promise<Rankings> {
    const response = await useFetch('/rankings', METHODS.GET);

    return (response.data as Rankings) ?? { reactivity: [], blabla: [], participations: [], jokers: [] };
}
