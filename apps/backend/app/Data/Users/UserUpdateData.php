<?php

namespace App\Data\Users;

use App\Enums\UserStatus;

final readonly class UserUpdateData
{
    /**
     * @param  array<string, mixed>  $personAttributes
     * @param  array<string, mixed>  $accountAttributes
     */
    public function __construct(
        public array $personAttributes,
        public array $accountAttributes,
        public ?UserStatus $status,
    ) {}
}
