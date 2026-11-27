<?php

namespace App\Actions\Auth;

use App\Enums\UserStatus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;

class SendPasswordResetLinkAction
{
    public function execute(string $email): void
    {
        (new Timebox)->call(
            fn () => $this->sendLink($email),
            max(0, (int) config('security.password_reset.min_response_ms')) * 1000,
        );
    }

    private function sendLink(string $email): void
    {
        $status = Password::broker()->sendResetLink(['email' => $email, 'status' => UserStatus::Active->value]);

        if ($status === Password::INVALID_USER) {
            Hash::make(Str::random(40));
        }
    }
}
