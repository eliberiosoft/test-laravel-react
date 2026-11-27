<?php

namespace App\Services\Auth;

use App\Enums\UserStatus;
use App\Models\User;

class AccountLockoutService
{
    public function registerFailure(User $user): bool
    {
        $attempts = min($user->failed_login_attempts + 1, FAILED_LOGIN_ATTEMPTS_MAX);
        $locksAccount = $attempts >= $this->maxFailedAttempts();

        $user->forceFill(['failed_login_attempts' => $attempts]);

        if ($locksAccount) {
            $user->forceFill([
                'status' => UserStatus::Blocked,
                'locked_at' => now(),
            ]);
        }

        $user->save();

        return $locksAccount;
    }

    public function unlock(User $user): void
    {
        $user->forceFill([
            'status' => UserStatus::Active,
            'failed_login_attempts' => 0,
            'locked_at' => null,
        ])->save();
    }

    public function reset(User $user): void
    {
        if ($user->failed_login_attempts === 0) {
            return;
        }

        $user->forceFill(['failed_login_attempts' => 0])->save();
    }

    private function maxFailedAttempts(): int
    {
        return min(max(1, (int) config('security.lockout.max_failed_attempts')), FAILED_LOGIN_ATTEMPTS_MAX);
    }
}
