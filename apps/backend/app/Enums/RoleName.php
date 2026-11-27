<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case User = 'user';

    /**
     * @return list<PermissionName>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => PermissionName::cases(),
            self::User => [
                PermissionName::WelcomeView,
                PermissionName::ProfileView,
                PermissionName::ProfileUpdate,
            ],
        };
    }
}
