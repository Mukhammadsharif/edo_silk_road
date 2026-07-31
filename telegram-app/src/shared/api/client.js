import { BASE_URL } from '../../config';

/**
 * HTTP-клиент mini app.
 *
 * Токен хранится на уровне модуля и выставляется AuthProvider'ом
 * после /api/telegram/auth — фичам не нужно таскать его через пропсы.
 */
let authToken = null;

export function setAuthToken(token) {
    authToken = token;
}

export class ApiError extends Error {
    constructor(message, status, data) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.data = data || null;
    }
}

/**
 * Запрос к API.
 *
 * @param {string} path            путь от корня, например '/api/telegram/counts'
 * @param {object} [options]
 * @param {string} [options.method]   GET по умолчанию
 * @param {object|FormData} [options.body]  объект сериализуется в JSON,
 *                                          FormData уходит как есть (для файлов)
 * @param {boolean} [options.auth]    слать ли Bearer-токен (по умолчанию true)
 */
export async function api(path, { method = 'GET', body, auth = true } = {}) {
    const headers = {};
    if (auth && authToken) headers['Authorization'] = 'Bearer ' + authToken;

    let payload = body;
    if (body && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        payload = JSON.stringify(body);
    }

    const resp = await fetch(BASE_URL + path, { method, headers, body: payload });

    let data = null;
    try { data = await resp.json(); } catch (e) { /* не-JSON ответ */ }

    if (!resp.ok) {
        const message = (data && (data.message || data.error)) || ('HTTP ' + resp.status);
        throw new ApiError(message, resp.status, data);
    }

    return data;
}
