<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthSessionResource;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request, LoginAction $login): AuthSessionResource
    {
        return AuthSessionResource::make($login->execute($request->toCredentials()));
    }
}
