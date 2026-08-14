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
        const headers = {
            status: response.status,
            ...Object.fromEntries(response.headers.entries())
        };

        const body = response.status === 204 ? null : await response.json().catch(() => null);

        if (!response.ok) {
            // A stale session should stop passing the router guard.
            if (response.status === 401) localStorage.removeItem('user');

            return {
                headers,
                data: null,
                errors: { message: body?.message ?? `Erreur ${response.status}`, error: body?.errors },
            };
        }

        return {
            headers,
            data: body?.data,
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

export interface PaginationMeta {
    current_page: number,
    last_page: number,
    per_page: number,
    total: number,
    from: number | null,
    to: number | null,
}

export interface PaginatedResponse<T = any> {
    data: T[],
    meta: PaginationMeta | null,
    errors?: { message: string },
}

/**
 * Fetch a paginated collection, preserving Laravel's top-level `meta`
 * (current_page, last_page, total…) that `useFetch` would otherwise drop.
 */
export async function usePaginatedFetch<T = any>(url: string): Promise<PaginatedResponse<T>> {
    try {
        const response = await fetch(apiEndpoint + url, {
            method: METHODS.GET,
            credentials: 'include',
            headers: { 'Accept': CONTENT_TYPE.JSON },
        });

        if (!response.ok) {
            return { data: [], meta: null, errors: { message: `Erreur ${response.status}` } };
        }

        const body = await response.json();

        return { data: body?.data ?? [], meta: body?.meta ?? null };
    } catch (error) {
        console.log(error);
        return { data: [], meta: null, errors: { message: 'An unknown error occurred' } };
    }
}

/**
 * POST a multipart/form-data body (file uploads). The browser sets the
 * multipart boundary itself, so we must NOT force a Content-Type header.
 */
export async function useUpload(
    url: string,
    body: FormData,
    headerOptions: HeadersInit = {}
): Promise<ApiResponse> {
    try {
        const response = await fetch(apiEndpoint + url, {
            method: METHODS.POST,
            credentials: 'include',
            headers: { 'Accept': CONTENT_TYPE.JSON, ...headerOptions },
            body,
        });

        const json = await response.json().catch(() => null);

        if (!response.ok) {
            return {
                headers: { status: response.status },
                data: null,
                errors: { message: json?.message ?? 'Upload failed', error: json?.errors },
            };
        }

        return { headers: { status: response.status }, data: json?.data };
    } catch (error) {
        console.log(error);
        return { headers: null, data: null, errors: { message: 'An unknown error occurred' } };
    }
}

export interface DownloadResult {
    ok: boolean,
    errors?: { message: string },
}

/**
 * Download a binary response (PDF, ZIP…) and trigger the browser's save
 * dialog via a temporary object URL.
 */
export async function useDownload(url: string, fallbackName = 'download'): Promise<DownloadResult> {
    try {
        const response = await fetch(apiEndpoint + url, {
            method: METHODS.GET,
            credentials: 'include',
        });

        if (!response.ok) {
            return { ok: false, errors: { message: `Erreur ${response.status}` } };
        }

        const blob = await response.blob();

        let filename = fallbackName;
        const disposition = response.headers.get('Content-Disposition');
        if (disposition) {
            const match = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(disposition);
            if (match) filename = decodeURIComponent(match[1]);
        }

        const objectUrl = URL.createObjectURL(blob);
        const anchor = document.createElement('a');
        anchor.href = objectUrl;
        anchor.download = filename;
        document.body.appendChild(anchor);
        anchor.click();
        anchor.remove();
        URL.revokeObjectURL(objectUrl);

        return { ok: true };
    } catch (error) {
        console.log(error);
        return { ok: false, errors: { message: 'An unknown error occurred' } };
    }
}