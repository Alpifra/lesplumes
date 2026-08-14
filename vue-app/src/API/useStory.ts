import { useFetch, useUpload, useDownload, METHODS } from "./useApi";
import { useXsrfToken } from "./useAuth";
import type { User } from "@/API/useUser";
import type { Round } from "./useRound";

export interface Media {
    id: number,
    url: string | null,
    extension: string,
    mime_type: string,
    size: number,
    created_at: string,
    updated_at: string,
}

export interface Story {
    id: number,
    title: string | null,
    round?: Round,
    writer: User,
    media: Media | null,
    updated_at: string,
    created_at: string,
}

const routePrefix = '/stories';

export function useStories() {
    return useFetch(routePrefix, METHODS.GET);
}

export async function useStory(id: number): Promise<Story> {
    const story = await useFetch(routePrefix + "/" + String(id), METHODS.GET);

    return story.data as Story;
}

/**
 * Deposit (or replace) the authenticated user's text on a round.
 */
export async function useCreateStory(
    roundId: number,
    payload: { title?: string, file: File }
) {
    const token = await useXsrfToken();

    const form = new FormData();
    if (payload.title) form.append('title', payload.title);
    form.append('file', payload.file);

    return useUpload(`/rounds/${roundId}/stories`, form, { 'X-XSRF-TOKEN': token ?? '' });
}

export function useDownloadStory(roundId: number, storyId: number, filename = 'texte.pdf') {
    return useDownload(`/rounds/${roundId}/stories/${storyId}/download`, filename);
}
