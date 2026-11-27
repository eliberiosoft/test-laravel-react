<?php

namespace App\Services\Auth;

use App\Data\Auth\LoginCredentials;
use App\Enums\LoginResult;
use App\Models\LoginAttempt;
use App\Models\User;

class LoginAttemptService
{
    public function record(LoginCredentials $credentials, LoginResult $result, ?User $user = null): void
    {
        LoginAttempt::create([
            'user_id' => $user?->getKey(),
            'identifier' => $credentials->identifier(),
            'result' => $result,
            'ip_address' => $credentials->ipAddress,
            'user_agent' => $credentials->userAgent,
            'attempted_at' => now(),
        ]);
    }
}
