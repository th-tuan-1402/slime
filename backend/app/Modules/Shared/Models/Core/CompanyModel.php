<?php

declare(strict_types=1);

namespace App\Modules\Shared\Models\Core;

use App\Modules\Shared\Models\BaseTenantModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Eloquent model for the `company` table.
 *
 * Stores company (tenant organization) master data.
 *
 * @property int $company_id
 */
class CompanyModel extends BaseTenantModel
{
    /** @var string */
    protected $table = 'company';

    /** @var string */
    protected $primaryKey = 'company_id';

    /** @var bool */
    public $incrementing = true;

    /** @var list<string> */
    protected $fillable = [];

    /** @var array<string, string> */
    protected $casts = [
        'company_id' => 'int',
    ];

    /**
     * @return HasMany<UserInfoModel, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserInfoModel::class, 'company_id', 'company_id');
    }
}
