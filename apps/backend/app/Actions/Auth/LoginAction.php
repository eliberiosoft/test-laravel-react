<?php

namespace App\Actions\Auth;

use App\Data\Auth\IssuedSession;
use App\Data\Auth\LoginCredentials;
use App\Enums\LoginResult;
use App\Exceptions\Auth\AuthenticationDeniedException;
use App\Models\User;
use App\Notifications\AccountLockedNotification;
use App\Services\Auth\AccountLockoutService;
use App\Services\Auth\LoginAttemptService;
use App\Services\Auth\SessionService;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;

class LoginAction
{
    public function __construct(
        private readonly Hasher $hasher,
        private readonly SessionService $sessions,
        private readonly AccountLockoutService $lockout,
        private readonly LoginAttemptService $attempts,
    ) {}

    /**
     * @throws AuthenticationDeniedException
     */
    public function execute(LoginCredentials $credentials): IssuedSession
    {
        return (new Timebox)->call(function (Timebox $timebox) use ($credentials): IssuedSession {
            $outcome = DB::transaction(
                fn (): IssuedSession|LoginResult => $this->attempt($credentials),
                DB_TRANSACTION_ATTEMPTS,
            );

            throw_if($outcome instanceof LoginResult, AuthenticationDeniedException::class, $outcome);

            $timebox->returnEarly();

            return $outcome;
        }, $this->minimumFailureDurationInMicroseconds());
    }

    private function minimumFailureDurationInMicroseconds(): int
    {
        return max(0, (int) config('security.login.min_failure_response_ms')) * 1000;
    }

    private function attempt(LoginCredentials $credentials): IssuedSession|LoginResult
    {
        $user = User::query()
            ->whereLogin($credentials->login)
            ->lockForUpdate()
            ->first();

        $passwordMatches = $this->passwordMatches($user, $credentials->password);

        if ($user === null) {
            return $this->reject($credentials, LoginResult::InvalidCredentials, null);
        }

        $denial = $user->status->denialResult();

        if ($denial !== null) {
            $this->attempts->record($credentials, $denial, $user);

            return LoginResult::InvalidCredentials;
        }

        if (! $passwordMatches) {
            if ($this->lockout->registerFailure($user)) {
                $user->notify(new AccountLockedNotification);
            }

            return $this->reject($credentials, LoginResult::InvalidCredentials, $user);
        }

        $this->rehashPasswordIfNeeded($user, $credentials->password);
        $this->lockout->reset($user);
        $this->sessions->closeStale($user);

        if ($this->sessions->hasActive($user)) {
            return $this->reject($credentials, LoginResult::ActiveSession, $user);
        }

        $session = $this->sessions->open($user, $credentials);
        $this->attempts->record($credentials, LoginResult::Success, $user);

        return $session;
    }

    private function passwordMatches(?User $user, string $password): bool
    {
        return $this->hasher->check($password, $user?->password ?? $this->dummyHash()) && $user !== null;
    }

    private function dummyHash(): string
    {
        $hash = Cache::get(LOGIN_DUMMY_HASH_CACHE_KEY);

        if (! is_string($hash) || $this->hasher->needsRehash($hash)) {
            $hash = $this->hasher->make(Str::random(40));
            Cache::forever(LOGIN_DUMMY_HASH_CACHE_KEY, $hash);
        }

        return $hash;
    }

    private function rehashPasswordIfNeeded(User $user, string $password): void
    {
        if ($this->hasher->needsRehash($user->password)) {
            $user->forceFill(['password' => $password])->save();
        }
    }

    private function reject(LoginCredentials $credentials, LoginResult $result, ?User $user): LoginResult
    {
        $this->attempts->record($credentials, $result, $user);

        return $result;
    }
}
