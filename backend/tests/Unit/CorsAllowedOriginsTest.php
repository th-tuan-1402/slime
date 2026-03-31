<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CorsAllowedOrigins;
use PHPUnit\Framework\TestCase;

final class CorsAllowedOriginsTest extends TestCase
{
    public function test_prod_rejects_wildcard_when_supports_credentials(): void
    {
        $this->assertSame(
            ['https://example.com'],
            CorsAllowedOrigins::build(
                raw: '*, https://example.com',
                isProduction: true,
                supportsCredentials: true,
                usedFallbackDefault: false
            )
        );
    }

    public function test_prod_requires_explicit_origin_list_when_fallback_default_was_used(): void
    {
        $this->assertSame(
            [],
            CorsAllowedOrigins::build(
                raw: 'http://localhost:3000',
                isProduction: true,
                supportsCredentials: true,
                usedFallbackDefault: true
            )
        );
    }

    public function test_non_prod_keeps_localhost_default(): void
    {
        $this->assertSame(
            ['http://localhost:3000'],
            CorsAllowedOrigins::build(
                raw: 'http://localhost:3000',
                isProduction: false,
                supportsCredentials: true,
                usedFallbackDefault: true
            )
        );
    }
}

