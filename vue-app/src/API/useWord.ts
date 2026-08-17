import { useFetch, METHODS } from "./useApi";
import { randomWord } from "@/utils/session";

const routePrefix = '/words';

/** The dictionary is stored in lower case; the card shows a proper noun. */
const capitalize = (word: string): string =>
    word.charAt(0).toUpperCase() + word.slice(1);

/**
 * A word drawn from the French dictionary for the session to come, skipping
 * the ones already played and the one the plume already has under her eyes.
 *
 * The dice button is a convenience, never a gate: should the API be
 * unreachable or the dictionary not yet imported, the built-in pool answers
 * rather than leaving the button dead.
 */
export async function useRandomWord(exclude = ''): Promise<string> {
    const query = exclude ? `?exclude=${encodeURIComponent(exclude)}` : '';
    const response = await useFetch(`${routePrefix}/random${query}`, METHODS.GET);
    const word = (response.data as { word?: string } | undefined)?.word;

    return word ? capitalize(word) : randomWord(exclude);
}
