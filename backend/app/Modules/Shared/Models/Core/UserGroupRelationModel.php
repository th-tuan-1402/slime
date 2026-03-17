<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Core;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the `user_group_relation` table.
 *
 * Maps users to user groups.
 *
 * @property int $user_id
 */
class UserGroupRelationModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'user_group_relation';

    /** @var string|null */
    protected $primaryKey = null;

    /** @var bool */
    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'user_id' => 'int',
    ];

    /**
     * @return BelongsTo<UserInfoModel, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserInfoModel::class, 'user_id', 'user_id');
    }

    /**
     * @return BelongsTo<UserGroupInfoModel, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(UserGroupInfoModel::class, 'user_group_id', 'user_group_id');
    }
}
