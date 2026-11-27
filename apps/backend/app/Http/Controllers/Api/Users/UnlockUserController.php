<?php

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\UnlockUserAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

class UnlockUserController extends Controller
{
    public function __invoke(User $user, UnlockUserAction $unlock): UserResource
    {
        return UserResource::make($unlock->execute($user)->load(['person', 'roles']));
    }
}
