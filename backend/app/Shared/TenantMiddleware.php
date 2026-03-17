<?php

declare(strict_types=1);

namespace App\Shared;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP middleware that enforces multi-tenant isolation.
 *
 * Every API request must carry an `X-Tenant-ID` header containing a valid
 * 7-character Crockford Base32 tenant identifier. Invalid or missing values
 * are rejected with a generic 404 to prevent tenant enumeration.
 */
class TenantMiddleware
{
    private const TENANT_HEADER = 'X-Tenant-ID';

    /** Crockford Base32 alphabet, 7 characters. */
    private const TENANT_ID_PATTERN = '/^[23456789abcdefghjkmnpqrstuvwxyz]{7}$/';

    public function __construct(
        private readonly TenantService $tenantService,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * Validates the tenant ID from the request header and delegates
     * database connection switching to TenantService.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header(self::TENANT_HEADER);

        if ($tenantId === null || !preg_match(self::TENANT_ID_PATTERN, $tenantId)) {
            abort(404);
        }

        $this->tenantService->connect($tenantId);

        return $next($request);
    }
}
