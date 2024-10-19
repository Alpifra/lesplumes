import { useFetch, METHODS } from "./useApi";

export function useLogin() {
    return useFetch('/login', METHODS.GET);
}
