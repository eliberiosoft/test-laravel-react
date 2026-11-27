<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case Blocked = 'blocked';
    case Inactive = 'inactive';

    public function denialResult(): ?LoginResult
    {
        return match ($this) {
            self::Active => null,
            self::Blocked => LoginResult::AccountBlocked,
            self::Inactive => LoginResult::AccountInactive,
        };
    }
}
