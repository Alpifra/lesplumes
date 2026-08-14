import { useFetch, METHODS } from "./useApi";
import { useXsrfToken } from "./useAuth";

/**
 * Invite a plume to join the circle by email.
 * Returns the API response ({ status: 'invited' | 'exists' } on success).
 */
export async function useInvite(email: string) {
    const token = await useXsrfToken();

    return useFetch('/invitations', METHODS.POST, { email }, { 'X-XSRF-TOKEN': token ?? '' });
}
