<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string|null $tenant_id
 * @property string|null $role
 */
final class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasApiTokens;
    use Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'tenant_id',
        'role',
        'visible_record_ids',
        'email',
        'password',
        'name',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];
}

