<?php

namespace App\Data\Users;

use App\Models\User;
use App\Models\UserSession;
use Carbon\CarbonInterface;

final readonly class WelcomeSummary
{
    public function __construct(
        public User $user,
        public ?UserSession $previousSession,
        public int $failedAttemptsSincePreviousSession,
        public ?CarbonInterface $lastFailedAttemptAt,
    ) {}
}
