<?php

namespace App\Enums;

enum LoginResult: string
{
    case Success = 'success';
    case InvalidCredentials = 'invalid_credentials';
    case AccountBlocked = 'account_blocked';
    case AccountInactive = 'account_inactive';
    case ActiveSession = 'active_session';

    /**
     * @return list<string>
     */
    public static function failureValues(): array
    {
        return array_values(array_map(
            fn (self $result): string => $result->value,
            array_filter(self::cases(), fn (self $result): bool => ! in_array($result, [self::Success, self::ActiveSession], true)),
        ));
    }
}
