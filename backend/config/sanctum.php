<?php

declare(strict_types=1);

return [
    /*
     * Sanctum authentication guard.
     */
    'guard' => ['sanctum'],

    /*
     * Domains that will receive stateful API authentication cookies.
     *
     * For now, keep it configurable via env for Nuxt dev/prod.
     */
    'stateful' => explode(',', (string) env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:3000')),

    /*
     * Personal access token expiration in minutes. Null means never expires.
     */
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION'),

    /*
     * Token prefix to improve secret scanning and reduce accidental leaks.
     */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];

