<?php

namespace App\Data\Auth;

use App\Enums\UserStatus;
use SensitiveParameter;

final readonly class PasswordResetData
{
    public function __construct(
        public string $email,
        #[SensitiveParameter] public string $token,
        #[SensitiveParameter] public string $password,
    ) {}

    /**
     * @return array<string, string>
     */
    public function credentials(): array
    {
        return [
            'email' => $this->email,
            'status' => UserStatus::Active->value,
            'token' => $this->token,
            'password' => $this->password,
        ];
    }
}
