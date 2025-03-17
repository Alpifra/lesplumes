import { useFetch, METHODS } from "./useApi";

export interface User {
    id: number,
    first_name: string,
    last_name: string,
    user_name: string,
    email: string,
    created_at: string,
    updated_at: string,
}

const routePrefix = '/users';

export function useUsers() {
    return useFetch(routePrefix, METHODS.GET);
}

export function useStorageUser(): User {

    const storedUser = localStorage.getItem('user');

    if (!storedUser) {
        throw new Error('User not found on local storage under "user" key.');
    }

    return JSON.parse(storedUser) as User;
}

export async function useUser(username: string): Promise<User> {

    const user = await useFetch(routePrefix + "/" + username, METHODS.GET);
    localStorage.setItem('user', JSON.stringify(user.data));

    return user.data as User;
}