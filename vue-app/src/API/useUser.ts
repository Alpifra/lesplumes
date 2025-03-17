import { useFetch, METHODS } from "./useApi";

const routePrefix = '/users';

export function useUsers() {
    return useFetch(routePrefix, METHODS.GET);
}

export function useUser(username: string) {
    return useFetch(routePrefix + "/" + username, METHODS.GET);
}