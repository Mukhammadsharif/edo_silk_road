<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://172.17.110.54:8055', 'http://127.0.0.1:8000', 'http://83.69.136.85:18082', '*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
