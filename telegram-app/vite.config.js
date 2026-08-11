import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

/**
 * Telegram Mini App EDOLINE (React).
 *
 * ВАЖНО: собираем в public/telegram-app-v2, а НЕ в public/telegram-app.
 * Старый монолитный index.html остаётся жив по прежнему URL — так можно
 * катить новый апп на отдельной ссылке в BotFather и откатиться в один шаг.
 *
 * base обязан совпадать с публичным путём: Vite подставляет его в ссылки
 * на /assets/*.js и *.css внутри index.html. Если оставить '/telegram-app/',
 * новый апп будет тянуть ассеты из папки старого и покажет белый экран.
 */
export default defineConfig({
    plugins: [react()],
    base: '/telegram-app-v2/',
    build: {
        outDir: '../public/telegram-app-v2',
        emptyOutDir: true,
    },
});
