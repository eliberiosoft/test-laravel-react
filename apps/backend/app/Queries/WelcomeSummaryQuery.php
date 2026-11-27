<?php

namespace App\Queries;

use App\Data\Users\WelcomeSummary;
use App\Enums\LoginResult;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Support\Carbon;

class WelcomeSummaryQuery
{
    public function for(User $user, ?int $currentTokenId): WelcomeSummary
    {
        $previousSession = $this->previousSession($user, $currentTokenId);

        $failures = LoginAttempt::query()
            ->where('user_id', $user->getKey())
            ->whereIn('result', LoginResult::failureValues())
            ->when($previousSession, fn ($query) => $query->where('attempted_at', '>=', $previousSession->started_at));

        $lastFailedAttemptAt = (clone $failures)->max('attempted_at');

        return new WelcomeSummary(
            user: $user->load(['person', 'roles']),
            previousSession: $previousSession,
            failedAttemptsSincePreviousSession: (clone $failures)->count(),
            lastFailedAttemptAt: $lastFailedAttemptAt === null ? null : Carbon::parse($lastFailedAttemptAt),
        );
    }

    private function previousSession(User $user, ?int $currentTokenId): ?UserSession
    {
        $currentSessionId = $currentTokenId === null
            ? null
            : $user->sessions()->where('token_id', $currentTokenId)->value('id');

        return $user->sessions()
            ->when($currentSessionId, fn ($query) => $query->whereKeyNot($currentSessionId))
            ->latest('started_at')
            ->latest('id')
            ->first();
    }
}
