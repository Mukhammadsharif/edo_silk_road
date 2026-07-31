import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { haptic, onTgEvent, syncTelegramChrome, tg } from '../../shared/telegram';

/**
 * Тема: auto | light | dark.
 * CSS остался прежним, поэтому классы вешаются на <body>:
 *   body.dark / body.light — ручной выбор
 *   body.tg-dark           — auto при тёмной теме Telegram
 */
const ThemeContext = createContext(null);

const THEME_KEY = 'tg_app_theme';

function readSavedTheme() {
    try { return localStorage.getItem(THEME_KEY) || 'auto'; } catch (e) { return 'auto'; }
}

export function ThemeProvider({ children }) {
    const [pref, setPrefState] = useState(readSavedTheme);
    // Тик для реакции на смену темы самого Telegram при pref='auto'
    const [tgSchemeTick, setTgSchemeTick] = useState(0);

    useEffect(() => onTgEvent('themeChanged', () => setTgSchemeTick((n) => n + 1)), []);

    useEffect(() => {
        const body = document.body;
        body.classList.remove('dark', 'light', 'tg-dark');

        let isDark = false;
        if (pref === 'dark') {
            body.classList.add('dark');
            isDark = true;
        } else if (pref === 'light') {
            body.classList.add('light');
        } else {
            // auto — по теме Telegram
            let tgDark = false;
            try { tgDark = tg.colorScheme === 'dark'; } catch (e) {}
            if (tgDark) body.classList.add('tg-dark');
            isDark = tgDark;
        }

        syncTelegramChrome(isDark);
    }, [pref, tgSchemeTick]);

    const setPref = useCallback((mode) => {
        haptic('light');
        try { localStorage.setItem(THEME_KEY, mode); } catch (e) {}
        setPrefState(mode);
    }, []);

    const value = useMemo(() => ({ pref, setPref }), [pref, setPref]);

    return <ThemeContext.Provider value={value}>{children}</ThemeContext.Provider>;
}

export function useTheme() {
    return useContext(ThemeContext);
}
