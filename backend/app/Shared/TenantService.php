<?php

declare(strict_types=1);

namespace App\Shared;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Resolves tenant database connections at runtime.
 *
 * Maps a tenant ID to its dedicated database name and reconfigures
 * the Eloquent default connection for the duration of the request.
 */
class TenantService
{
    private const CONNECTION_NAME = 'tenant';
    private const DB_PREFIX = 'ht_';

    private ?string $currentTenantId = null;

    /**
     * Switch the default Eloquent connection to the given tenant's database.
     *
     * Overwrites the `tenant` connection config, purges any stale connection,
     * reconnects, and sets `tenant` as the default connection.
     */
    public function connect(string $tenantId): void
    {
        $dbName = $this->resolveDbName($tenantId);

        Config::set('database.connections.' . self::CONNECTION_NAME . '.database', $dbName);

        DB::purge(self::CONNECTION_NAME);
        DB::reconnect(self::CONNECTION_NAME);

        Config::set('database.default', self::CONNECTION_NAME);

        $this->currentTenantId = $tenantId;
    }

    /**
     * Return the tenant ID that was resolved for the current request.
     */
    public function getCurrentTenantId(): ?string
    {
        return $this->currentTenantId;
    }

    /**
     * Map a tenant ID to its database name.
     */
    private function resolveDbName(string $tenantId): string
    {
        return self::DB_PREFIX . $tenantId;
    }
}
