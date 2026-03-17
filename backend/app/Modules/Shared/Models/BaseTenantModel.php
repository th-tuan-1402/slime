<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Abstract base for all models in the tenant database.
 *
 * Sets connection to 'tenant' and disables auto-incrementing
 * timestamps by default (legacy tables use custom timestamp columns).
 */
abstract class BaseTenantModel extends Model
{
    /** @var string */
    protected $connection = 'tenant';

    /** Legacy tables do not use Laravel's created_at / updated_at. */
    public $timestamps = false;
}
