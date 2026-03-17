<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Record;

use App\Modules\Shared\Models\BaseTenantModel;
use App\Modules\Shared\Models\Core\UserInfoModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for dynamic `record_<schema_id>` tables.
 *
 * Each DbSchema has a corresponding record table whose name is
 * resolved at runtime via the static forSchema() factory.
 *
 * @property int $record_id
 * @property int|null $parent_record_id
 * @property int|null $record_outer_id
 * @property int|null $regist_user_id
 * @property \Illuminate\Support\Carbon|null $regist_date
 * @property int|null $update_user_id
 * @property \Illuminate\Support\Carbon|null $update_date
 * @property int|null $fix_flag
 * @property int|null $approval_flow_id
 * @property int|null $approval_point_id
 * @property int|null $approval_status_id
 */
class DynamicRecordModel extends BaseTenantModel
{
    /** @var string */
    protected $primaryKey = 'record_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'record_id' => 'int',
        'parent_record_id' => 'int',
        'record_outer_id' => 'int',
        'regist_user_id' => 'int',
        'regist_date' => 'datetime',
        'update_user_id' => 'int',
        'update_date' => 'datetime',
        'fix_flag' => 'int',
        'approval_flow_id' => 'int',
        'approval_point_id' => 'int',
        'approval_status_id' => 'int',
    ];

    /**
     * Factory method that returns a model instance bound to the
     * correct record table for the given schema.
     */
    public static function forSchema(int $schemaId): static
    {
        $instance = new static();
        $instance->setTable('record_' . $schemaId);

        return $instance;
    }

    /**
     * @return BelongsTo<UserInfoModel, $this>
     */
    public function registUser(): BelongsTo
    {
        return $this->belongsTo(UserInfoModel::class, 'regist_user_id', 'user_id');
    }

    /**
     * @return BelongsTo<UserInfoModel, $this>
     */
    public function updateUser(): BelongsTo
    {
        return $this->belongsTo(UserInfoModel::class, 'update_user_id', 'user_id');
    }
}
