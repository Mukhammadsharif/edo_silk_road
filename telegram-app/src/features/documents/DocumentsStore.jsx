import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import { DocumentsApi, MyDocumentsApi } from '../../shared/api/endpoints';
import { useI18n } from '../../app/providers/I18nProvider';
import { CATEGORIES } from './constants';

/**
 * Состояние вкладки «Документы»: счётчики главной, кэш списков
 * по категориям, липкие фильтры sign-категорий, опции статусов.
 *
 * Поведение перенесено из старой версии:
 *  - фильтр my_docs сбрасывается при каждом входе в категорию;
 *  - фильтры sign-категорий ЛИПКИЕ (переживают навигацию), дефолт
 *    приходит с сервера и ставится, пока пользователь не тронул фильтр;
 *  - смена языка сбрасывает кэш опций статусов и списков
 *    (лейблы из БД языкозависимы);
 *  - защита от гонок: рендерится только ответ последнего запроса.
 */
const DocumentsStoreContext = createContext(null);

const emptyCache = () => ({ items: [], page: 1, hasMore: false });

export function DocumentsStoreProvider({ children }) {
    const { t, backendLang } = useI18n();

    const [counts, setCounts] = useState({ for_resolution: 0, for_sign: 0, for_approval: 0, my_docs: 0 });

    const [lists, setLists] = useState({
        for_resolution: emptyCache(),
        for_sign:       emptyCache(),
        for_approval:   emptyCache(),
        my_docs:        emptyCache(),
    });
    const [listStatus, setListStatus] = useState({});   // { [cat]: 'loading'|'ready'|'error' }
    const [listError, setListError] = useState({});     // { [cat]: message }

    const [myDocsFilter, setMyDocsFilterState] = useState('');
    const [signFilters, setSignFilters] = useState({ for_resolution: '2', for_sign: '2', for_approval: '2' });
    const signFiltersTouched = useRef({});

    const [statusOptions, setStatusOptions] = useState({}); // { [cat]: [{code,label}] }

    // Порядковый номер запроса на категорию — защита от гонок при быстрых тапах
    const requestSeq = useRef({});

    /* ─────────── Смена языка: языкозависимые кэши в мусор ─────────── */
    useEffect(() => {
        setStatusOptions({});
        setLists({
            for_resolution: emptyCache(),
            for_sign:       emptyCache(),
            for_approval:   emptyCache(),
            my_docs:        emptyCache(),
        });
        setListStatus({});
    }, [backendLang]);

    /* ─────────── Счётчики главной ─────────── */
    const loadCounts = useCallback(async () => {
        const data = await DocumentsApi.counts();
        setCounts({
            for_resolution: data.for_resolution || 0,
            for_sign:       data.for_sign || 0,
            for_approval:   data.for_approval || 0,
            my_docs:        data.my_docs || 0,
        });
    }, []);

    /* ─────────── Опции статусов для sign-категорий ─────────── */
    const ensureStatusOptions = useCallback(async (cat) => {
        if (statusOptions[cat]) return statusOptions[cat];

        const role = CATEGORIES[cat].role;
        let opts;
        try {
            const data = await DocumentsApi.statusOptions(role, backendLang);
            opts = [{ code: 'all', label: t('mini.all') }].concat(
                (data.options || []).map((o) => ({ code: String(o.code), label: o.label }))
            );

            const serverDefault = data.default || '2';
            // Дефолт сервера применяем, только если пользователь ещё не трогал фильтр
            if (!signFiltersTouched.current[cat]) {
                setSignFilters((prev) => ({ ...prev, [cat]: serverDefault }));
            }
            // Дефолтную опцию — в начало (самый частый случай)
            const idx = opts.findIndex((o) => o.code === serverDefault);
            if (idx > 1) {
                const def = opts.splice(idx, 1)[0];
                opts.unshift(def);
            }
        } catch (e) {
            opts = [
                { code: 'all', label: t('mini.all') },
                { code: '2',   label: t('mini.st_pending') },
            ];
        }

        setStatusOptions((prev) => ({ ...prev, [cat]: opts }));
        return opts;
    }, [statusOptions, backendLang, t]);

    /* ─────────── Загрузка списка категории ─────────── */
    const loadCategory = useCallback(async (cat, { reset = false, filterOverride } = {}) => {
        const seq = (requestSeq.current[cat] || 0) + 1;
        requestSeq.current[cat] = seq;

        const page = reset ? 1 : (lists[cat].page + (lists[cat].items.length ? 1 : 0)) || 1;

        setListStatus((prev) => ({ ...prev, [cat]: 'loading' }));

        try {
            let items, hasMore;

            if (cat === 'my_docs') {
                const status = filterOverride !== undefined ? filterOverride : myDocsFilter;
                const data = await MyDocumentsApi.list(reset ? 1 : page, status, backendLang);
                const fresh = data.data || [];
                items = reset ? fresh : lists[cat].items.concat(fresh);
                hasMore = !!(data.meta && data.meta.has_more);
            } else {
                const stepStatus = filterOverride !== undefined
                    ? filterOverride
                    : (signFilters[cat] || '2');
                const data = await DocumentsApi.list(CATEGORIES[cat].role, stepStatus, backendLang);
                items = data.documents || [];
                hasMore = false;
            }

            // Ответ устарел — пользователь успел уйти/сменить фильтр
            if (requestSeq.current[cat] !== seq) return;

            setLists((prev) => ({
                ...prev,
                [cat]: { items, page: reset ? 1 : page, hasMore },
            }));
            setListStatus((prev) => ({ ...prev, [cat]: 'ready' }));
        } catch (e) {
            if (requestSeq.current[cat] !== seq) return;
            setListError((prev) => ({ ...prev, [cat]: e.message || t('mini.err_load') }));
            setListStatus((prev) => ({ ...prev, [cat]: 'error' }));
        }
    }, [lists, myDocsFilter, signFilters, backendLang, t]);

    const loadMore = useCallback(async (cat) => {
        if (!lists[cat].hasMore) return;
        // page уже учитывается в loadCategory через reset=false
        const nextPage = lists[cat].page + 1;
        const seq = (requestSeq.current[cat] || 0) + 1;
        requestSeq.current[cat] = seq;
        try {
            const data = await MyDocumentsApi.list(nextPage, myDocsFilter, backendLang);
            if (requestSeq.current[cat] !== seq) return;
            setLists((prev) => ({
                ...prev,
                [cat]: {
                    items: prev[cat].items.concat(data.data || []),
                    page: nextPage,
                    hasMore: !!(data.meta && data.meta.has_more),
                },
            }));
        } catch (e) { /* кнопка останется, можно повторить */ }
    }, [lists, myDocsFilter, backendLang]);

    /* ─────────── Фильтры ─────────── */
    const setMyDocsFilter = useCallback((status) => {
        setMyDocsFilterState(status);
        loadCategory('my_docs', { reset: true, filterOverride: status });
    }, [loadCategory]);

    const setSignFilter = useCallback((cat, value) => {
        signFiltersTouched.current[cat] = true;
        setSignFilters((prev) => ({ ...prev, [cat]: value }));
        loadCategory(cat, { reset: true, filterOverride: value });
    }, [loadCategory]);

    /** Вход в категорию: my_docs сбрасывает фильтр, sign — липкий. */
    const enterCategory = useCallback((cat) => {
        if (cat === 'my_docs') {
            setMyDocsFilterState('');
            loadCategory('my_docs', { reset: true, filterOverride: '' });
        } else {
            ensureStatusOptions(cat);
            loadCategory(cat, { reset: true });
        }
    }, [loadCategory, ensureStatusOptions]);

    /* ─────────── Мутации после действий ─────────── */
    const removeMyDocFromCache = useCallback((jId) => {
        setLists((prev) => ({
            ...prev,
            my_docs: { ...prev.my_docs, items: prev.my_docs.items.filter((d) => d.id !== jId) },
        }));
        setCounts((prev) => ({ ...prev, my_docs: Math.max(0, prev.my_docs - 1) }));
    }, []);

    const invalidateMyDocs = useCallback(() => {
        setLists((prev) => ({ ...prev, my_docs: emptyCache() }));
        setCounts((prev) => ({ ...prev, my_docs: prev.my_docs + 1 }));
    }, []);

    const value = useMemo(() => ({
        counts, loadCounts,
        lists, listStatus, listError,
        myDocsFilter, setMyDocsFilter,
        signFilters, setSignFilter,
        statusOptions, ensureStatusOptions,
        enterCategory, loadCategory, loadMore,
        removeMyDocFromCache, invalidateMyDocs,
    }), [
        counts, loadCounts, lists, listStatus, listError,
        myDocsFilter, setMyDocsFilter, signFilters, setSignFilter,
        statusOptions, ensureStatusOptions, enterCategory, loadCategory, loadMore,
        removeMyDocFromCache, invalidateMyDocs,
    ]);

    return <DocumentsStoreContext.Provider value={value}>{children}</DocumentsStoreContext.Provider>;
}

export function useDocumentsStore() {
    return useContext(DocumentsStoreContext);
}
