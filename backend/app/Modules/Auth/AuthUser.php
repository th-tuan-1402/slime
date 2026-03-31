<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Enums\RoleKey;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Authenticated user model backed by the tenant `user_info` table.
 *
 * @property int $user_id
 * @property string $login_id
 * @property string $user_name
 * @property int $administrator_flag
 * @property int $delete_flag
 * @property-read string $role Derived from {@see administrator_flag} for API authorization.
 */
final class AuthUser extends Authenticatable
{
    use HasApiTokens;

    /** @var string */
    protected $table = 'user_info';

    /** @var string */
    protected $primaryKey = 'user_id';

    /** @var bool */
    public $incrementing = true;

    /** @var string */
    protected $keyType = 'int';

    /** @var list<string> */
    protected $hidden = [];

    /** @var array<string, string> */
    protected $casts = [
        'user_id' => 'int',
        'administrator_flag' => 'int',
        'delete_flag' => 'int',
    ];

    /**
     * Map legacy Hanbai administrator flag to string roles expected by {@see \App\Http\AbstractApiController::authorizeRole()}.
     */
    public function getRoleAttribute(): string
    {
        if ((int) ($this->attributes['administrator_flag'] ?? 0) === 1) {
            return RoleKey::Admin->value;
        }

        // Default seeded local account: broad access for import/export during development & QA.
        return RoleKey::Manager->value;
    }
}

