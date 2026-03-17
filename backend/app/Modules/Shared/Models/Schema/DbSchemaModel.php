<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Schema;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model for the `db_schema` table.
 *
 * Represents a user-defined database schema (entity type) within the tenant.
 * Each schema produces a corresponding `record_<db_schema_id>` table.
 *
 * @property int $db_schema_id
 * @property int $dbg_id
 */
class DbSchemaModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'db_schema';

    /** @var string */
    protected $primaryKey = 'db_schema_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'db_schema_id' => 'int',
        'dbg_id' => 'int',
    ];

    /**
     * @return BelongsTo<DbGroupModel, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(DbGroupModel::class, 'dbg_id', 'dbg_id');
    }

    /**
     * @return HasMany<DbFieldModel, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(DbFieldModel::class, 'db_schema_id', 'db_schema_id');
    }

    /**
     * Scope: filter schemas by group.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeByGroup(Builder $query, int $dbgId): Builder
    {
        return $query->where('dbg_id', '=', $dbgId);
    }
}
