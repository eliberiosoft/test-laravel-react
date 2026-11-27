<?php

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\CreateUserAction;
use App\Actions\Users\DeleteUserAction;
use App\Actions\Users\UpdateUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\IndexUsersRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Queries\UserIndexQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(IndexUsersRequest $request, UserIndexQuery $users): AnonymousResourceCollection
    {
        return UserResource::collection($users->paginated($request->perPage()));
    }

    public function store(StoreUserRequest $request, CreateUserAction $create): JsonResponse
    {
        $user = $create->execute($request->toData());

        return UserResource::make($user->load(['person', 'roles']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user): UserResource
    {
        Gate::authorize('view', $user);

        return UserResource::make($user->load(['person', 'roles']));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $update): UserResource
    {
        return UserResource::make($update->execute($user, $request->toData())->load(['person', 'roles']));
    }

    public function destroy(User $user, DeleteUserAction $delete): Response
    {
        Gate::authorize('delete', $user);

        $delete->execute($user);

        return response()->noContent();
    }
}
