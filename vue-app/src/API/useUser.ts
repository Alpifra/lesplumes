import { useFetch, METHODS } from "./useApi";

export interface User {
    id: number,
    first_name: string,
    last_name: string,
    user_name: string,
    email: string,
    // Set once the plume left the circle; she still shows in past sessions.
    deactivated_at?: string | null,
    created_at: string,
    updated_at: string,
}

const routePrefix = '/users';

export function useUsers() {
    return useFetch(routePrefix, METHODS.GET);
}

export function useStorageUser(): User | null {

    const storedUser = localStorage.getItem('user');

    if (!storedUser) return null;

    try {
        return JSON.parse(storedUser) as User;
    } catch {
        localStorage.removeItem('user');
        return null;
    }
}

export async function useUser(username: string): Promise<User> {

    const user = await useFetch(routePrefix + "/" + username, METHODS.GET);

    if (user.data) {
        localStorage.setItem('user', JSON.stringify(user.data));
    }

    return user.data as User;
}