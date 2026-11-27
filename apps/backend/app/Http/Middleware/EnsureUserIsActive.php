<?php

namespace App\Http\Middleware;

use App\Enums\SessionEndReason;
use App\Exceptions\Auth\AuthenticationDeniedException;
use App\Models\User;
use App\Services\Auth\SessionService;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function __construct(private readonly SessionService $sessions) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $denial = $user instanceof User ? $user->status->denialResult() : null;

        if ($denial !== null) {
            $token = $user->currentAccessToken();

            if ($token instanceof PersonalAccessToken) {
                $this->sessions->close($token, SessionEndReason::Revoked);
            }

            throw new AuthenticationDeniedException($denial);
        }

        return $next($request);
    }
}
