<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 1C (employee-cabinet) integration
    |--------------------------------------------------------------------------
    | EDOLINE одновременно:
    |  - ПРИЁМНИК: 1С шлёт push на /main/api-1c/v1/{entity}/create_update
    |  - КЛИЕНТ: EDOLINE отправляет свои данные обратно в 1С (тот же формат)
    */

    // Базовый URL API 1С (для исходящих запросов EDOLINE → 1С)
    'base_url' => env('ONEC_BASE_URL', 'http://1c.local/api'),

    // Учётка EDOLINE в 1С (для авторизации исходящих запросов)
    'username' => env('ONEC_USERNAME', ''),
    'password' => env('ONEC_PASSWORD', ''),

    // Токен, которым 1С авторизуется при push в EDOLINE (входящие).
    // Сверяется в middleware. Можно несколько через запятую.
    'inbound_tokens' => env('ONEC_INBOUND_TOKENS', ''),

    'timeout' => (int) env('ONEC_TIMEOUT', 30),

    'log_requests' => (bool) env('ONEC_LOG_REQUESTS', true),

    // Формат дат в 1С API
    'date_format' => 'd-m-Y',
];
