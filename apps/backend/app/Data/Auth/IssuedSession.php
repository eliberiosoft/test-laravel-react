<?php

namespace App\Data\Auth;

use App\Models\User;
use Carbon\CarbonImmutable;
use SensitiveParameter;

final readonly class IssuedSession
{
    public function __construct(
        #[SensitiveParameter] public string $plainTextToken,
        public CarbonImmutable $expiresAt,
        public User $user,
    ) {}
}
