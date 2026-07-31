/**
 * Единая точка доступа к Telegram WebApp SDK.
 * Фолбэк-стаб для браузера подставляется в index.html,
 * поэтому здесь window.Telegram гарантированно существует.
 */
export const tg = window.Telegram.WebApp;

tg.ready();
tg.expand();

/** Тактильный отклик. type: light|medium|heavy|success|error|warning */
export function haptic(type) {
    if (!tg || !tg.HapticFeedback) return;
    try {
        if (type === 'success' || type === 'error' || type === 'warning') {
            tg.HapticFeedback.notificationOccurred(type);
        } else {
            tg.HapticFeedback.impactOccurred(type || 'light');
        }
    } catch (e) { /* no-op */ }
}

/** Цвета шапки/фона/нижней панели Telegram под текущую тему. */
export function syncTelegramChrome(isDark) {
    try {
        if (tg.setHeaderColor) tg.setHeaderColor(isDark ? '#0f1629' : '#f5f7fb');
        if (tg.setBackgroundColor) tg.setBackgroundColor(isDark ? '#0f1629' : '#f5f7fb');
        if (tg.setBottomBarColor) tg.setBottomBarColor(isDark ? '#1e293b' : '#ffffff');
    } catch (e) { /* no-op */ }
}

/** Открыть внешнюю ссылку (браузер устройства). */
export function openExternalLink(url) {
    try { tg.openLink(url); } catch (e) { window.open(url, '_blank'); }
}

/** Подписка на событие Telegram, возвращает функцию отписки. */
export function onTgEvent(event, handler) {
    try { tg.onEvent(event, handler); } catch (e) { /* no-op */ }
    return () => {
        try { tg.offEvent(event, handler); } catch (e) { /* no-op */ }
    };
}
