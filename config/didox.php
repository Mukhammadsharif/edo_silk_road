<?php

/*
|--------------------------------------------------------------------------
| Didox integration (EDOLINE ↔ Didox)
|--------------------------------------------------------------------------
| EDOLINE выступает КЛИЕНТОМ Didox: авторизуется по ЭЦП или паролю,
| хранит user-токен (360 мин) в didox_tokens, шлёт запросы к API Didox
| с заголовками user-key + Partner-Authorization.
|
| Все значения — через env, чтобы тест/прод переключались без правок кода.
*/

return [

    // Базовый URL API Didox.
    //   Тест: https://testapi3.didox.uz  (для E-IMZO/оферты — https://stage.goodsign.biz)
    //   Прод: https://api-partners.didox.uz
    'base_url' => rtrim(env('DIDOX_BASE_URL', 'https://testapi3.didox.uz'), '/'),

    // Партнёрский токен (выдаёт аккаунт-менеджер Didox). Идёт в заголовке
    // Partner-Authorization на КАЖДЫЙ запрос.
    'partner_token' => env('DIDOX_PARTNER_TOKEN', ''),

    // Локаль по умолчанию для эндпоинтов :locale (ru|uz).
    'locale' => env('DIDOX_LOCALE', 'ru'),

    // Таймаут HTTP-запросов, сек.
    'timeout' => (int) env('DIDOX_TIMEOUT', 30),

    // Логировать запросы/ответы в канал didox.
    'log_requests' => (bool) env('DIDOX_LOG_REQUESTS', true),

    /*
    | Время жизни user-токена Didox. По документации — 360 минут.
    | Обновляем чуть раньше, чтобы не поймать протухший токен в момент запроса.
    */
    'token_ttl_minutes'        => (int) env('DIDOX_TOKEN_TTL', 360),
    'token_refresh_skew_minutes' => (int) env('DIDOX_TOKEN_SKEW', 10),

    /*
    | Webhook входящих: секрет, которым Didox (или прокси) подписывает push
    | о новом входящем документе. Сверяется в middleware/контроллере.
    | Можно несколько через запятую.
    */
    'webhook_secrets' => env('DIDOX_WEBHOOK_SECRETS', ''),

    // Формат дат в API Didox.
    'date_format' => 'Y-m-d',
];
