<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Application user roles.
 *
 * String-backed enum representing all possible roles a user can hold.
 * Values correspond to the role identifiers stored in the database.
 */
enum RoleKey: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Staff = 'staff';
    case ReadOnly = 'readonly';
}
