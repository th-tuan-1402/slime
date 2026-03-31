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

        $corsAllowedOrigins = env('CORS_ALLOWED_ORIGINS');
        $frontendUrl = env('FRONTEND_URL');
        $clientUrl = env('CLIENT_URL');

        $raw = (string) ($corsAllowedOrigins ?? $frontendUrl ?? $clientUrl ?? $defaultFrontendUrl);

        $appEnv = (string) env('APP_ENV', 'production');
        $isProduction = $appEnv === 'production';
        $usedFallbackDefault = $corsAllowedOrigins === null && $frontendUrl === null && $clientUrl === null;

        return \App\Support\CorsAllowedOrigins::build(
            raw: $raw,
            isProduction: $isProduction,
            supportsCredentials: true,
            usedFallbackDefault: $usedFallbackDefault
        );
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
