import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { AuthApi } from '../../shared/api/endpoints';
import { setAuthToken } from '../../shared/api/client';
import { haptic, tg } from '../../shared/telegram';
import { useI18n } from './I18nProvider';

/**
 * Авторизация по Telegram initData.
 *
 * status: 'loading' | 'ready' | 'not-linked' | 'error'
 * not-linked — аккаунт Telegram не привязан к пользователю EDOLINE
 * (нужен /start в боте с логином/паролем).
 */
const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const { t } = useI18n();
    const [status, setStatus] = useState('loading');
    const [user, setUser] = useState(null);
    const [errorMessage, setErrorMessage] = useState('');

    const login = useCallback(async () => {
        setStatus('loading');
        setErrorMessage('');
        try {
            const initData = tg.initData;
            if (!initData) {
                setErrorMessage(t('mini.err_only_telegram'));
                setStatus('error');
                return;
            }

            const data = await AuthApi.auth(initData);
            setAuthToken(data.token);
            setUser(data.user || null);
            setStatus('ready');
        } catch (e) {
            if (e.data && e.data.error === 'not_linked') {
                setStatus('not-linked');
                return;
            }
            haptic('error');
            setErrorMessage(e.message || t('mini.err_connect'));
            setStatus('error');
        }
    }, [t]);

    useEffect(() => { login(); }, []); // eslint-disable-line react-hooks/exhaustive-deps

    // Нижняя навигация видима только после авторизации
    // (CSS: body:not(.authenticated) .bottom-nav { display: none })
    useEffect(() => {
        document.body.classList.toggle('authenticated', status === 'ready');
        document.body.classList.toggle('with-bottom-nav', status === 'ready');
    }, [status]);

    const value = useMemo(
        () => ({ status, user, errorMessage, retry: login }),
        [status, user, errorMessage, login]
    );

    return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
    return useContext(AuthContext);
}
