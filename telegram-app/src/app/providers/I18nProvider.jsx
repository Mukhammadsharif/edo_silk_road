import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { I18nApi } from '../../shared/api/endpoints';
import { DICTIONARY } from '../../shared/i18n/dictionary';
import { haptic } from '../../shared/telegram';

/**
 * i18n: uz / cyr / ru / en.
 *
 * Источник переводов — локальный DICTIONARY (dictionary.js). Эндпоинт
 * /api/telegram/translations на бэкенде НЕ обязателен: если он есть,
 * его ответ ПЕРЕКРОЕТ локальные значения по совпадающим ключам;
 * если его нет (404) — приложение просто работает на локальном словаре.
 *
 * Смена языка = setState → React перерисует дерево; императивного
 * обхода DOM (applyI18n) из старой версии больше нет.
 */
const I18nContext = createContext(null);

const LANG_KEY = 'tg_app_lang';

export const LANG_OPTIONS = [
    { code: 'uz',  label: "O'zbekcha" },
    { code: 'cyr', label: 'Ўзбекча' },
    { code: 'ru',  label: 'Русский' },
    { code: 'en',  label: 'English' },
];

function readSavedLang() {
    try { return localStorage.getItem(LANG_KEY) || 'uz'; } catch (e) { return 'uz'; }
}

export function I18nProvider({ children }) {
    const [lang, setLangState] = useState(readSavedLang);
    const [remoteDict, setRemoteDict] = useState(null);

    // Необязательная попытка подтянуть переводы с сервера.
    // 404 — штатная ситуация, никаких ошибок в интерфейсе.
    useEffect(() => {
        I18nApi.translations()
            .then((data) => {
                if (data && data.translations) setRemoteDict(data.translations);
            })
            .catch(() => { /* эндпоинта нет — работаем на локальном словаре */ });
    }, []);

    /**
     * Слияние: серверные значения главнее, но НЕ затирают локальные
     * языки, которых сервер не прислал. Это важно для `cyr` — контроллер
     * генерирует кириллицу автотранслитерацией из латиницы, и если по
     * какому-то ключу он её не отдал, останется ручной вариант отсюда.
     */
    const dict = useMemo(() => {
        if (!remoteDict) return DICTIONARY;
        const merged = { ...DICTIONARY };
        Object.keys(remoteDict).forEach((key) => {
            const incoming = remoteDict[key] || {};
            const clean = {};
            Object.keys(incoming).forEach((lng) => {
                if (incoming[lng]) clean[lng] = incoming[lng];
            });
            merged[key] = { ...(DICTIONARY[key] || {}), ...clean };
        });
        return merged;
    }, [remoteDict]);

    // <html lang> для доступности (cyr — тоже узбекский)
    useEffect(() => {
        try { document.documentElement.setAttribute('lang', lang === 'cyr' ? 'uz' : lang); } catch (e) {}
    }, [lang]);

    const t = useCallback((key) => {
        const entry = dict[key];
        if (!entry) return key;
        // cyr отсутствует в серверных переводах — падаем на латиницу
        return entry[lang] || entry.uz || entry.en || key;
    }, [dict, lang]);

    /** Код языка для данных с бэкенда: cyr → uz (данные остаются латиницей). */
    const backendLang = lang === 'cyr' ? 'uz' : lang;

    const setLang = useCallback((code) => {
        haptic('light');
        try { localStorage.setItem(LANG_KEY, code); } catch (e) {}
        setLangState(code);
    }, []);

    const value = useMemo(
        () => ({ lang, backendLang, setLang, t }),
        [lang, backendLang, setLang, t]
    );

    return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n() {
    return useContext(I18nContext);
}
