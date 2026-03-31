<?php

declare(strict_types=1);

return [
    /*
     * Sanctum authentication guard.
     */
    'guard' => ['web'],

    /*
     * Domains that will receive stateful API authentication cookies.
     *
     * For now, keep it configurable via env for Nuxt dev/prod.
     */
    'stateful' => (static function (): array {
        $clientPort = (string) env('CLIENT_PORT', '3000');
        $default = implode(',', [
            'localhost',
            'localhost:' . $clientPort,
            '127.0.0.1',
            '127.0.0.1:' . $clientPort,
        ]);

        return explode(',', (string) env('SANCTUM_STATEFUL_DOMAINS', $default));
    })(),

    /*
     * Personal access token expiration in minutes. Null means never expires.
     */
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION'),

    /*
     * Token prefix to improve secret scanning and reduce accidental leaks.
     */
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
];

