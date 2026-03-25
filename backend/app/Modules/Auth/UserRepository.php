<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

final class UserRepository
{
    private const PASSWORD_TYPE_VALID = 1;

    public function findActiveUserByLoginId(string $loginId): ?AuthUser
    {
        /** @var AuthUser|null $user */
        $user = AuthUser::query()
            ->where('login_id', '=', $loginId)
            ->where('delete_flag', '=', 0)
            ->first();

        return $user;
    }

    public function findCurrentValidPasswordHash(int $userId): ?string
    {
        /** @var object|null $row */
        $row = DB::table('password_info')
            ->select(['password'])
            ->where('user_id', '=', $userId)
            ->where('password_type', '=', self::PASSWORD_TYPE_VALID)
            ->orderByDesc('regist_date')
            ->first();

        if ($row === null) {
            return null;
        }

        /** @var string|null $password */
        $password = $row->password ?? null;

        return $password;
    }

    public function isAccountLocked(int $userId, CarbonImmutable $now): bool
    {
        if ((bool) config('auth_module.account_lock_enabled', true) === false) {
            return false;
        }

        /** @var object|null $row */
        $row = DB::table('account_lock')
            ->select(['lock_flag', 'failure_date', 'failure_count'])
            ->where('user_id', '=', $userId)
            ->first();

        if ($row === null) {
            return false;
        }

        $lockFlag = (int) ($row->lock_flag ?? 0);
        if ($lockFlag === 0) {
            return false;
        }

        $unlockPeriodMinutes = (int) config('auth_module.account_unlock_period_minutes', 30);

        $failureDateRaw = $row->failure_date ?? null;
        if ($failureDateRaw === null) {
            return true;
        }

        $failureDate = CarbonImmutable::parse((string) $failureDateRaw);
        $unlockAt = $failureDate->addMinutes($unlockPeriodMinutes);

        return $now->lt($unlockAt);
    }

    public function recordLoginFailure(int $userId, CarbonImmutable $now): void
    {
        if ((bool) config('auth_module.account_lock_enabled', true) === false) {
            return;
        }

        $failureLimit = (int) config('auth_module.login_failure_limit', 5);
        $resetMinutes = (int) config('auth_module.login_failure_reset_time_minutes', 30);

        /** @var object|null $row */
        $row = DB::table('account_lock')
            ->select(['failure_count', 'failure_date', 'lock_flag'])
            ->where('user_id', '=', $userId)
            ->first();

        $failureCount = 0;
        $failureDate = $now;
        $lockFlag = 0;

        if ($row !== null) {
            $rawDate = $row->failure_date ?? null;
            if ($rawDate !== null) {
                $existingDate = CarbonImmutable::parse((string) $rawDate);
                $resetAt = $existingDate->addMinutes($resetMinutes);

                if ($now->gte($resetAt)) {
                    $failureCount = 0;
                } else {
                    $failureCount = (int) ($row->failure_count ?? 0);
                    $failureDate = $existingDate;
                }
            }
        }

        $failureCount++;
        $failureDate = $now;

        if ($failureCount >= $failureLimit) {
            $lockFlag = 1;
        }

        DB::table('account_lock')->updateOrInsert(
            ['user_id' => $userId],
            [
                'failure_count' => $failureCount,
                'failure_date' => $failureDate->toDateTimeString(),
                'lock_flag' => $lockFlag,
            ],
        );
    }

    public function clearAccountLock(int $userId): void
    {
        DB::table('account_lock')
            ->where('user_id', '=', $userId)
            ->update([
                'failure_count' => 0,
                'lock_flag' => 0,
            ]);
    }
}

