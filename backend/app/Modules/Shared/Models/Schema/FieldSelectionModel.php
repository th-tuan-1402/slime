<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Schema;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the `field_selection` table.
 *
 * Stores selection (dropdown/radio) options for a field.
 *
 * @property int $field_id
 */
class FieldSelectionModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'field_selection';

    /** @var string */
    protected $primaryKey = 'field_id';

    /** @var bool */
    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
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
