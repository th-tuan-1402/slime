<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Core;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model for the `user_group_info` table.
 *
 * Stores user group definitions.
 *
 * @property int $user_group_id
 */
class UserGroupInfoModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'user_group_info';

    /** @var string */
    protected $primaryKey = 'user_group_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'user_group_id' => 'int',
    ];

    /**
     * @return HasMany<UserGroupRelationModel, $this>
     */
    public function relations(): HasMany
    {
        return $this->hasMany(UserGroupRelationModel::class, 'user_group_id', 'user_group_id');
    }
}
