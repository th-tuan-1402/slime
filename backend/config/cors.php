<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    | Only apply CORS to API routes and the Sanctum CSRF cookie endpoint.
    */
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => (static function (): array {
        $clientPort = (string) env('CLIENT_PORT', '3000');
        $defaultFrontendUrl = 'http://localhost:' . $clientPort;

        $raw = (string) env(
            'CORS_ALLOWED_ORIGINS',
            env('FRONTEND_URL', env('CLIENT_URL', $defaultFrontendUrl))
        );

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    })(),

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'Authorization',
        'X-Requested-With',
        'X-Tenant-ID',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => true,
];
