<?php

namespace App\Actions\Auth;

use App\Data\Auth\PasswordResetData;
use App\Enums\SessionEndReason;
use App\Exceptions\Auth\InvalidPasswordResetTokenException;
use App\Models\User;
use App\Services\Auth\SessionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use SensitiveParameter;

class ResetPasswordAction
{
    public function __construct(private readonly SessionService $sessions) {}

    /**
     * @throws InvalidPasswordResetTokenException
     */
    public function execute(PasswordResetData $data): void
    {
        $status = DB::transaction(function () use ($data): string {
            User::query()->where('email', $data->email)->lockForUpdate()->first();

            return Password::broker()->reset(
                $data->credentials(),
                fn (User $user, #[SensitiveParameter] string $password) => $this->replacePassword($user, $password),
            );
        }, DB_TRANSACTION_ATTEMPTS);

        throw_unless($status === Password::PASSWORD_RESET, InvalidPasswordResetTokenException::class);
    }

    private function replacePassword(User $user, #[SensitiveParameter] string $password): void
    {
        $user->forceFill(['password' => $password, 'failed_login_attempts' => 0])->save();

        $this->sessions->closeAll($user, SessionEndReason::Revoked);
    }
}
