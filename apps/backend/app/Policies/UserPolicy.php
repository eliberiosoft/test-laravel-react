<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can(PermissionName::UsersView->value);
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->is($target) || $actor->can(PermissionName::UsersView->value);
    }

    public function create(User $actor): bool
    {
        return $actor->can(PermissionName::UsersCreate->value);
    }

    public function update(User $actor, User $target): bool
    {
        return $actor->can(PermissionName::UsersUpdate->value) && $this->isManageable($actor, $target);
    }

    public function delete(User $actor, User $target): bool
    {
        return $actor->can(PermissionName::UsersDelete->value) && $this->isManageable($actor, $target);
    }

    public function unlock(User $actor, User $target): bool
    {
        return $actor->can(PermissionName::UsersUnlock->value) && ! $actor->is($target);
    }

    public function revokeSessions(User $actor, User $target): bool
    {
        return $actor->can(PermissionName::SessionsRevoke->value) && $this->isManageable($actor, $target);
    }

    public function import(User $actor): bool
    {
        return $actor->can(PermissionName::UsersImport->value);
    }

    private function isManageable(User $actor, User $target): bool
    {
        return ! $actor->is($target) && ! $target->hasRole(RoleName::Admin->value);
    }
}
