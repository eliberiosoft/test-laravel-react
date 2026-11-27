<?php

namespace App\Services\Auth;

use App\Data\Auth\IssuedSession;
use App\Data\Auth\LoginCredentials;
use App\Enums\SessionEndReason;
use App\Models\User;
use App\Models\UserSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;
use LogicException;

class SessionService
{
    public function hasActive(User $user): bool
    {
        return $user->sessions()->active()->exists();
    }

    public function closeStale(User $user): void
    {
        $user->sessions()->open()->where('expires_at', '<=', now())->update([
            'ended_at' => DB::raw('expires_at'),
            'end_reason' => SessionEndReason::Expired->value,
        ]);

        $user->sessions()->open()->whereNull('token_id')->update([
            'ended_at' => now(),
            'end_reason' => SessionEndReason::Revoked->value,
        ]);
    }

    public function open(User $user, LoginCredentials $credentials): IssuedSession
    {
        $startedAt = CarbonImmutable::now();
        $expiresAt = $startedAt->addMinutes($this->lifetimeInMinutes());

        $token = $user->createToken(AUTH_TOKEN_NAME, AUTH_TOKEN_ABILITIES, $expiresAt);

        $user->sessions()->create([
            'token_id' => $token->accessToken->getKey(),
            'ip_address' => $credentials->ipAddress,
            'user_agent' => $credentials->userAgent,
            'started_at' => $startedAt,
            'expires_at' => $expiresAt,
        ]);

        return new IssuedSession($token->plainTextToken, $expiresAt, $user);
    }

    public function close(PersonalAccessToken $token, SessionEndReason $reason): void
    {
        DB::transaction(function () use ($token, $reason): void {
            UserSession::query()
                ->open()
                ->where('token_id', $token->getKey())
                ->update([
                    'ended_at' => now(),
                    'end_reason' => $reason->value,
                ]);

            $token->delete();
        }, DB_TRANSACTION_ATTEMPTS);
    }

    public function isExpiredToken(?string $plainTextToken): bool
    {
        if ($plainTextToken === null) {
            return false;
        }

        $token = PersonalAccessToken::findToken($plainTextToken);

        return $token?->expires_at !== null && $token->expires_at->isPast();
    }

    public function closeAll(User $user, SessionEndReason $reason): void
    {
        $user->sessions()->open()->update([
            'ended_at' => now(),
            'end_reason' => $reason->value,
        ]);

        $user->tokens()->delete();
    }

    private function lifetimeInMinutes(): int
    {
        $minutes = (int) config('sanctum.expiration');

        throw_if($minutes <= 0, LogicException::class, 'sanctum.expiration must be a positive number of minutes.');

        return $minutes;
    }
}
