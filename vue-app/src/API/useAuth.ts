import router from "@/router";
import { useFetch, METHODS } from "./useApi";
import { useUser } from "./useUser";

interface loginData {
    username: string,
    password: string
}

const appEndpoint = import.meta.env.VITE_APPLICATION_ENDPOINT;

const getCookie = (cookieName: string) => {

    const rawCookies = document.cookie;

    if (!rawCookies) return null;

    const cookies = rawCookies.split(';')
        .map(v => v.split('='))
        .reduce((acc: { [key: string]: string }, v) => {
            acc[decodeURIComponent(v[0].trim())] = decodeURIComponent(v[1].trim());
            return acc;
        }, {});

    return cookies[cookieName] ?? null
}

const removeCookie = (cookieName: string) => {
    document.cookie = `${encodeURIComponent(cookieName)}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`;
};

export async function useXsrfToken() {

    const token = getCookie('XSRF-TOKEN');

    if (token) return token;

    const credentials: RequestCredentials = 'include';
    const request = {
        method: METHODS.GET,
        credentials: credentials,
        headers: { 'Content-Type': 'application/json' }
    };

    const response: any = await fetch(appEndpoint + '/sanctum/csrf-cookie', request)
        .then(res => res)
        .then(content => content);

    if (!response.ok) console.log(response);

    return getCookie('XSRF-TOKEN');
}

export async function useLogin(data: loginData) {

    const token = await useXsrfToken();
    const login = await useFetch('/login', METHODS.POST, data, { 'X-XSRF-TOKEN': token ?? '' });

    if (login.errors) return login;

    return useUser(data.username);
}

export async function useLogout() {

    const token = await useXsrfToken();
    const logout = await useFetch('/logout', METHODS.POST, undefined, { 'X-XSRF-TOKEN': token ?? '' });

    if (logout.errors) return logout;

    localStorage.removeItem('user');
    removeCookie('X-XSRF-TOKEN');

    router.push('/connexion');
};
