<?php

namespace App\Http\Controllers\Api\Users;

use App\Actions\Users\RevokeUserSessionsAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Response;

class RevokeUserSessionsController extends Controller
{
    public function __invoke(User $user, RevokeUserSessionsAction $revoke): Response
    {
        $revoke->execute($user);

        return response()->noContent();
    }
}
