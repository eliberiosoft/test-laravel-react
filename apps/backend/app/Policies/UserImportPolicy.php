<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\User;
use App\Models\UserImport;

class UserImportPolicy
{
    public function view(User $actor, UserImport $import): bool
    {
        return $actor->can(PermissionName::UsersImport->value) && $actor->is($import->user);
    }
}
