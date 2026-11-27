<?php

namespace App\Actions\Auth;

use App\Enums\SessionEndReason;
use App\Models\User;
use App\Services\Auth\SessionService;
use Laravel\Sanctum\PersonalAccessToken;

class LogoutAction
{
    public function __construct(private readonly SessionService $sessions) {}

    public function execute(User $user): void
    {
        $token = $user->currentAccessToken();

        if (! $token instanceof PersonalAccessToken) {
            return;
        }

        $this->sessions->close($token, SessionEndReason::Logout);
    }
}
