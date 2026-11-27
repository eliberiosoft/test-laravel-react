<?php

namespace App\Data\Auth;

use Illuminate\Support\Str;
use SensitiveParameter;

final readonly class LoginCredentials
{
    public function __construct(
        public string $login,
        #[SensitiveParameter] public string $password,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
    ) {}

    public function identifier(): string
    {
        return Str::of($this->login)
            ->trim()
            ->lower()
            ->substr(0, LOGIN_IDENTIFIER_MAX_LENGTH)
            ->toString();
    }
}
