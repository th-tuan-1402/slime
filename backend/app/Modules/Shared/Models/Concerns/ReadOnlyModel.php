<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Concerns;

use RuntimeException;

/**
 * Prevents write operations on database VIEW models.
 */
trait ReadOnlyModel
{
    /**
     * @param array<string, mixed> $attributes
     */
    public static function create(array $attributes = []): never
    {
        throw new RuntimeException(static::class . ' is read-only (database VIEW).');
    }

    /**
     * @param array<string, mixed> $options
     */
    public function save(array $options = []): never
    {
        throw new RuntimeException(static::class . ' is read-only (database VIEW).');
    }

    public function delete(): never
    {
        throw new RuntimeException(static::class . ' is read-only (database VIEW).');
    }
}
