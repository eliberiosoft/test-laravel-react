<?php

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateProfileRequest;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request, UpdateProfileAction $update): UserResource
    {
        $user = $update->execute($request->user(), $request->toData());

        return UserResource::make($user->load(['person', 'roles']));
    }
}
