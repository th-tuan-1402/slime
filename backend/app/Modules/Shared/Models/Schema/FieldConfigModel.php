<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Schema;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the `field_configs` table.
 *
 * Stores configuration settings for individual fields.
 *
 * @property int $config_id
 * @property int $field_id
 */
class FieldConfigModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'field_configs';

    /** @var string */
    protected $primaryKey = 'config_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'config_id' => 'int',
        'field_id' => 'int',
    ];

    /**
     * @return BelongsTo<DbFieldModel, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(DbFieldModel::class, 'field_id', 'field_id');
    }
}
