import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

/**
 * Telegram Mini App EDOLINE.
 *
 * Папка telegram-app/ лежит в КОРНЕ Laravel-проекта (рядом с app/, public/).
 * `npm run build` собирает прямо в public/telegram-app — тот же URL,
 * что и раньше: https://edoline.uz/telegram-app/
 *
 * emptyOutDir: true — public/telegram-app полностью перезаписывается
 * при каждой сборке (старый index.html туда класть больше не нужно).
 */
export default defineConfig({
  plugins: [react()],
  base: '/telegram-app/',
  build: {
    outDir: '../public/telegram-app',
    emptyOutDir: true,
  },
});
