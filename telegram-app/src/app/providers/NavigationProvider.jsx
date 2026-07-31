import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { haptic, tg } from '../../shared/telegram';

/**
 * Навигация mini app: вкладка (tab) + стек экранов внутри вкладки.
 *
 * Экраны (screen.name):
 *   documents: 'home' → 'list' (params.category) → 'detail'
 *              (params.kind: 'sign'|'my', params.id, params.docHash)
 *   create:    'create-type' → 'create-wizard'
 *   profile:   'profile'
 *
 * Новый экран = запись в ROOT_SCREENS/push + компонент в ScreenRouter.
 *
 * Аппаратная кнопка «назад» Telegram (BackButton) показывается,
 * когда в стеке больше одного экрана, и делает pop() — в старой
 * HTML-версии её не было вовсе.
 */
const NavigationContext = createContext(null);

const ROOT_SCREENS = {
    documents: { name: 'home', params: {} },
    create:    { name: 'create-type', params: {} },
    profile:   { name: 'profile', params: {} },
};

export function NavigationProvider({ children }) {
    const [tab, setTabState] = useState('documents');
    const [stacks, setStacks] = useState({
        documents: [ROOT_SCREENS.documents],
        create:    [ROOT_SCREENS.create],
        profile:   [ROOT_SCREENS.profile],
    });

    const stack = stacks[tab];
    const screen = stack[stack.length - 1];

    const push = useCallback((name, params = {}) => {
        setStacks((prev) => ({
            ...prev,
            [tab]: [...prev[tab], { name, params }],
        }));
    }, [tab]);

    const pop = useCallback(() => {
        setStacks((prev) => {
            const cur = prev[tab];
            if (cur.length <= 1) return prev;
            return { ...prev, [tab]: cur.slice(0, -1) };
        });
    }, [tab]);

    /** Заменить текущий экран (например detail → detail другого документа). */
    const replace = useCallback((name, params = {}) => {
        setStacks((prev) => {
            const cur = prev[tab];
            return { ...prev, [tab]: [...cur.slice(0, -1), { name, params }] };
        });
    }, [tab]);

    /** Сбросить стек вкладки до корня (например после создания документа). */
    const resetTab = useCallback((tabName, screens) => {
        setStacks((prev) => ({
            ...prev,
            [tabName]: screens && screens.length ? screens : [ROOT_SCREENS[tabName]],
        }));
    }, []);

    const setTab = useCallback((next) => {
        if (next === tab) {
            // Повторный тап по активной вкладке — к корню (привычный мобильный паттерн)
            setStacks((prev) => ({ ...prev, [next]: [ROOT_SCREENS[next]] }));
            return;
        }
        haptic('light');
        setTabState(next);
        // Вкладка «документы» всегда открывается с главной — как в старой версии
        if (next === 'documents') {
            setStacks((prev) => ({ ...prev, documents: [ROOT_SCREENS.documents] }));
        }
    }, [tab]);

    // Telegram BackButton ↔ стек
    useEffect(() => {
        const canGoBack = stack.length > 1;
        const handler = () => { haptic('light'); pop(); };
        try {
            if (canGoBack) {
                tg.BackButton.onClick(handler);
                tg.BackButton.show();
            } else {
                tg.BackButton.hide();
            }
        } catch (e) { /* стаб в браузере */ }
        return () => {
            try { tg.BackButton.offClick(handler); } catch (e) {}
        };
    }, [stack.length, pop]);

    const value = useMemo(
        () => ({ tab, setTab, screen, stack, push, pop, replace, resetTab }),
        [tab, setTab, screen, stack, push, pop, replace, resetTab]
    );

    return <NavigationContext.Provider value={value}>{children}</NavigationContext.Provider>;
}

export function useNavigation() {
    return useContext(NavigationContext);
}
