import { ref } from "vue";

const apiEndpoint = import.meta.env.VITE_API_ENDPOINT

export enum METHODS {
    GET = 'GET',
    POST = 'POST',
    PATCH = 'PATCH',
    DELETE = 'DELETE',
}

export async function useFetch(url: string, method: METHODS, body = {}) {
    const data = ref(null);
    const error = ref(null);

    const request = {
        methods: method,
        body: JSON.stringify(body),
        headers: {
            "Content-Type": "application/json",
        },
    };

    fetch(apiEndpoint + url, request)
        .then((res) => res.json())
        .then((json) => (data.value = json))
        .catch((err) => (error.value = err));

    return { data, error };
}
