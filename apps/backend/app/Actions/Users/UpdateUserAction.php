<?php

namespace App\Actions\Users;

use App\Data\Users\UserUpdateData;
use App\Enums\SessionEndReason;
use App\Enums\UserStatus;
use App\Exceptions\Users\UserIsBlockedException;
use App\Models\User;
use App\Services\Auth\SessionService;
use Illuminate\Support\Facades\DB;

class UpdateUserAction
{
    public function __construct(private readonly SessionService $sessions) {}

    /**
     * @throws UserIsBlockedException
     */
    public function execute(User $user, UserUpdateData $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            throw_if($data->status !== null && $locked->isBlocked(), UserIsBlockedException::class);

            $locked->person->update($data->personAttributes);
            $locked->fill($data->accountAttributes);

            if ($data->status !== null) {
                $locked->forceFill(['status' => $data->status]);
            }

            $locked->save();

            if ($locked->wasChanged('status') && $locked->status === UserStatus::Inactive) {
                $this->sessions->closeAll($locked, SessionEndReason::Revoked);
            }

            return $locked;
        }, DB_TRANSACTION_ATTEMPTS);
    }
}
