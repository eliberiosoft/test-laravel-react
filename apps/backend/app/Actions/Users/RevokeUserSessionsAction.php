<?php

namespace App\Actions\Users;

use App\Enums\SessionEndReason;
use App\Models\User;
use App\Services\Auth\SessionService;
use Illuminate\Support\Facades\DB;

class RevokeUserSessionsAction
{
    public function __construct(private readonly SessionService $sessions) {}

    public function execute(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $this->sessions->closeAll($user, SessionEndReason::Revoked);

            activity('users')
                ->performedOn($user)
                ->event('sessions_revoked')
                ->log('Sesiones revocadas por un administrador');
        }, DB_TRANSACTION_ATTEMPTS);
    }
}
