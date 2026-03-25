<?php

declare(strict_types=1);

namespace App\Modules\Auth;

final class LoginResultModel
{
    /**
     * @param array<string, mixed>|null $user
     */
    private function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly ?array $user,
        public readonly ?string $token,
    ) {
    }

    /**
     * @param array<string, mixed> $user
     */
    public static function success(array $user, string $token): self
    {
        return new self(true, 'OK', $user, $token);
    }

    public static function failure(string $message): self
    {
        return new self(false, $message, null, null);
    }
}

