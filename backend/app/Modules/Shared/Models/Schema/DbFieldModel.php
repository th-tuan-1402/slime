<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Schema;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model for the `db_field` table.
 *
 * Represents a field definition within a database schema.
 *
 * @property int $field_id
 * @property int $db_schema_id
 */
class DbFieldModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'db_field';

    /** @var string */
    protected $primaryKey = 'field_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'field_id' => 'int',
        'db_schema_id' => 'int',
    ];

    /**
     * @return BelongsTo<DbSchemaModel, $this>
     */
    public function schema(): BelongsTo
    {
        return $this->belongsTo(DbSchemaModel::class, 'db_schema_id', 'db_schema_id');
    }

    /**
     * @return HasMany<FieldConfigModel, $this>
     */
    public function configs(): HasMany
    {
        return $this->hasMany(FieldConfigModel::class, 'field_id', 'field_id');
    }

    /**
     * @return HasMany<FieldSelectionModel, $this>
     */
    public function selections(): HasMany
    {
        return $this->hasMany(FieldSelectionModel::class, 'field_id', 'field_id');
    }
}
