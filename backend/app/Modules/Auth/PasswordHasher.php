<?php

declare(strict_types=1);

namespace App\Modules\Auth;

final class PasswordHasher
{
    /**
     * Legacy compatible hashing.
     *
     * Port of `PasswordUtil::getHashLoginPassword(md5(password), userId)`.
     */
    public static function hashLoginPassword(string $plainPassword, int $userId): string
    {
        $md5Password = md5($plainPassword);

        $loopCnt = 10000;
        $salt = md5((string) $userId);
        $result = '';

        for ($i = 0; $i < $loopCnt; $i++) {
            $result = hash('sha256', $result . $salt . $md5Password);
        }

        return $result;
    }
}

