<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Core;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Eloquent model for the `account_lock` table.
 *
 * Tracks account lockout state for a user.
 *
 * @property int $user_id
 */
class AccountLockModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'account_lock';

    /** @var string */
    protected $primaryKey = 'user_id';

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
}
