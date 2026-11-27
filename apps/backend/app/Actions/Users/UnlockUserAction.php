<?php

namespace App\Actions\Users;

use App\Exceptions\Users\UserNotBlockedException;
use App\Models\User;
use App\Services\Auth\AccountLockoutService;
use Illuminate\Support\Facades\DB;

class UnlockUserAction
{
    public function __construct(private readonly AccountLockoutService $lockout) {}

    /**
     * @throws UserNotBlockedException
     */
    public function execute(User $user): User
    {
        return DB::transaction(function () use ($user): User {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            throw_unless($locked->isBlocked(), UserNotBlockedException::class);

            $this->lockout->unlock($locked);

            return $locked;
        }, DB_TRANSACTION_ATTEMPTS);
    }
}
