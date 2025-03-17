const apiEndpoint = import.meta.env.VITE_API_ENDPOINT;

export interface ApiResponse {
    headers: { status: number } | null,
    data: {} | null,
    errors?: { error?: any, message: string }
}

export enum METHODS {
    GET = 'GET',
    POST = 'POST',
    PATCH = 'PATCH',
    DELETE = 'DELETE',
};

export enum CONTENT_TYPE {
    JSON = 'application/json',
    TEXT = 'text/plain',
    HTML = 'text/html',
    JPG = 'image/jpeg',
    PNG = 'image/png',
    SVG = 'image/svg+xml',
};

export async function useFetch(
    url: string,
    method: METHODS = METHODS.GET,
    body = {},
    headerOptions: HeadersInit = {}
) : Promise<ApiResponse> {

    const credentials: RequestCredentials = 'include';
    const request = {
        method: method,
        credentials: credentials,
        ...(method !== METHODS.GET && { body: JSON.stringify(body) }),
        headers: {
            'Content-Type': CONTENT_TYPE.JSON,
            'Accept': CONTENT_TYPE.JSON,
            ...headerOptions
        },
    };

    try {
        const response = await fetch(apiEndpoint + url, request);
        const data = response.status === 200 || response.status === 201 ? await response.json() : null;
        let headers = {
            status: response.status,
            ...Object.fromEntries(response.headers.entries())
        };

        return {
            headers,
            data: data,
        };
    } catch (error) {
        console.log(error);
        return {
            headers: null,
            data: null,
            errors: { message: 'An unknown error occurred' },
        };
    }
}