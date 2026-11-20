<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS',
        env('APP_ENV') === 'production' ? '' : env(
            'MOBILE_CORS_ORIGINS',
            'https://localhost,http://localhost,http://localhost:5173,http://127.0.0.1:5173'
        )
    )))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
