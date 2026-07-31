import { api } from './client';

/**
 * Вся API-поверхность mini app в одном месте.
 * Новая фича = новый блок здесь + свой каталог в features/.
 */

export const AuthApi = {
    /** Авторизация по Telegram initData → { token, user } | 401 not_linked */
    auth: (initData) =>
        api('/api/telegram/auth', { method: 'POST', body: { init_data: initData }, auth: false }),
};

export const I18nApi = {
    translations: () => api('/api/telegram/translations', { auth: false }),
};

export const DocumentsApi = {
    counts: () => api('/api/telegram/counts'),

    /** Списки для подписи/согласования/резолюции (role: RESOL|SIGN|AGREE) */
    list: (role, stepStatus, lang) =>
        api(`/api/telegram/documents?category=${role}&step_status=${encodeURIComponent(stepStatus)}&lang=${lang}`),

    detail: (id, lang) => api(`/api/telegram/documents/${id}?lang=${lang}`),

    /** Одноразовая ссылка на страницу подписи E-IMZO */
    signToken: (id) =>
        api(`/api/telegram/documents/${id}/sign-token`, { method: 'POST', body: {} }),

    /** Опции фильтра статусов шага для категории */
    statusOptions: (role, lang) =>
        api(`/api/telegram/status-options?category=${role}&lang=${lang}`),
};

export const MyDocumentsApi = {
    list: (page, status, lang) => {
        let url = `/api/telegram/my-documents?page=${page}&lang=${lang}`;
        if (status) url += `&status=${encodeURIComponent(status)}`;
        return api(url);
    },

    detail: (jId) => api(`/api/telegram/my-documents/${jId}`),

    remove: (jId) => api(`/api/telegram/my-documents/${jId}`, { method: 'DELETE' }),

    /** Отправка (на подпись/согласование/резолюцию) → { sign_url } */
    send: (jId, signType) =>
        api(`/api/telegram/my-documents/${jId}/send`, { method: 'POST', body: { sign_type: signType } }),
};

export const ProfileApi = {
    get: () => api('/api/telegram/profile'),

    uploadAvatar: (file) => {
        const fd = new FormData();
        fd.append('avatar', file);
        return api('/api/telegram/profile/avatar', { method: 'POST', body: fd });
    },

    deleteAvatar: () => api('/api/telegram/profile/avatar', { method: 'DELETE' }),

    changePassword: (current, next, confirmation) =>
        api('/api/telegram/profile/password', {
            method: 'POST',
            body: {
                current_password: current,
                new_password: next,
                new_password_confirmation: confirmation,
            },
        }),
};

export const CreateApi = {
    /** Справочники под тип документа: journals, confidences, languages, has_subscription */
    init: (type) => api(`/api/telegram/create/init/${encodeURIComponent(type)}`),

    /** Подтипы журнала */
    journals: (journalId) => api(`/api/telegram/create/journals/${journalId}`),

    /** Участники маршрута по типу журнала */
    executionUsers: (journalId) => api(`/api/telegram/create/execution-users/${journalId}`),

    /** Внешние организации (входящие/исходящие) */
    docOrganizations: (journalId) => api(`/api/telegram/create/doc-organizations/${journalId}`),

    /** Создание документа (FormData: поля + файлы) */
    store: (formData) => api('/api/telegram/create/store', { method: 'POST', body: formData }),

    /** Handoff в OnlyOffice web-editor на компьютере → { url } */
    webEditor: (data) => api('/api/telegram/create/web-editor', { method: 'POST', body: data }),
};
