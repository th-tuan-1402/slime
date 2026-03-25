<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use Carbon\CarbonImmutable;

final class AuthEditor
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function login(string $loginId, string $password): LoginResultModel
    {
        $user = $this->userRepository->findActiveUserByLoginId($loginId);
        if ($user === null) {
            return LoginResultModel::failure('Invalid credentials.');
        }

        if ($this->userRepository->isAccountLocked($user->user_id, now: CarbonImmutable::now())) {
            return LoginResultModel::failure('Account is locked.');
        }

        $currentPasswordHash = $this->userRepository->findCurrentValidPasswordHash($user->user_id);
        if ($currentPasswordHash === null) {
            return LoginResultModel::failure('Invalid credentials.');
        }

        $inputHash = PasswordHasher::hashLoginPassword($password, $user->user_id);
        if (!hash_equals($currentPasswordHash, $inputHash)) {
            $this->userRepository->recordLoginFailure($user->user_id, now: CarbonImmutable::now());

            if ($this->userRepository->isAccountLocked($user->user_id, now: CarbonImmutable::now())) {
                return LoginResultModel::failure('Account is locked.');
            }

            return LoginResultModel::failure('Invalid credentials.');
        }

        $this->userRepository->clearAccountLock($user->user_id);

        $tokenName = 'api';
        $token = $user->createToken($tokenName)->plainTextToken;

        return LoginResultModel::success(
            user: $this->toUserPayload($user),
            token: $token,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toUserPayload(AuthUser $user): array
    {
        return [
            'user_id' => (int) $user->user_id,
            'user_name' => (string) ($user->user_name ?? ''),
            'login_id' => (string) ($user->login_id ?? ''),
            'administrator_flag' => (int) ($user->administrator_flag ?? 0),
        ];
    }

    public function refreshToken(AuthUser $user): string
    {
        $tokenName = 'api';

        $token = $user->createToken($tokenName)->plainTextToken;

        return $token;
    }
}

