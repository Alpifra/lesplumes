import { useFetch, METHODS } from "./useApi";

const routePrefix = '/users';

export function useUsers() {
    return useFetch(routePrefix, METHODS.GET);
}

export function useUser(id: number) {
    return useFetch(routePrefix + "/" + String(id), METHODS.GET);
}