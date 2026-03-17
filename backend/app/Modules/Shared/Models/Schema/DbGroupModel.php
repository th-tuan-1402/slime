<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Schema;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model for the `db_group` table.
 *
 * Groups related database schemas together.
 *
 * @property int $dbg_id
 */
class DbGroupModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'db_group';

    /** @var string */
    protected $primaryKey = 'dbg_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'dbg_id' => 'int',
    ];

    /**
     * @return HasMany<DbSchemaModel, $this>
     */
    public function schemas(): HasMany
    {
        return $this->hasMany(DbSchemaModel::class, 'dbg_id', 'dbg_id');
    }
}
