<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Core;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Eloquent model for the `user_info` table.
 *
 * Stores user account information for the tenant.
 *
 * @property int $user_id
 * @property int $company_id
 */
class UserInfoModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'user_info';

    /** @var string */
    protected $primaryKey = 'user_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'user_id' => 'int',
        'company_id' => 'int',
    ];

    /**
     * @return BelongsTo<CompanyModel, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id', 'company_id');
    }

    /**
     * @return HasMany<UserGroupRelationModel, $this>
     */
    public function userGroupRelations(): HasMany
    {
        return $this->hasMany(UserGroupRelationModel::class, 'user_id', 'user_id');
    }

    /**
     * @return HasMany<PasswordInfoModel, $this>
     */
    public function passwords(): HasMany
    {
        return $this->hasMany(PasswordInfoModel::class, 'user_id', 'user_id');
    }

    /**
     * @return HasMany<RoleInfoModel, $this>
     */
    public function roles(): HasMany
    {
        return $this->hasMany(RoleInfoModel::class, 'user_id', 'user_id');
    }

    /**
     * @return HasOne<AccountLockModel, $this>
     */
    public function accountLock(): HasOne
    {
        return $this->hasOne(AccountLockModel::class, 'user_id', 'user_id');
    }

    /**
     * Scope: filter to active users only.
     *
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('delete_flag', '=', 0);
    }
}
