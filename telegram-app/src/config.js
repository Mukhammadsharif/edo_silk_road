/**
 * Базовый URL API. Для локальной разработки можно переопределить
 * через .env: VITE_API_BASE_URL=http://localhost:8000
 */
export const BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://edoline.uz';
