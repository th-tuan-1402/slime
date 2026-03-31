<?php

declare(strict_types=1);

namespace App\Support;

final class CorsAllowedOrigins
{
    /**
     * @return list<string>
     */
    public static function build(
        string $raw,
        bool $isProduction,
        bool $supportsCredentials,
        bool $usedFallbackDefault
    ): array {
        if ($isProduction && $usedFallbackDefault) {
            return [];
        }

        $origins = array_values(array_filter(array_map('trim', explode(',', $raw))));

        if ($isProduction && $supportsCredentials) {
            $origins = array_values(array_filter(
                $origins,
                static fn (string $origin): bool => $origin !== '*'
            ));
        }

        return $origins;
    }
}

