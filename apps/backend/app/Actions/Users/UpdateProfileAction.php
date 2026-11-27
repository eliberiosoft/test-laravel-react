<?php

namespace App\Actions\Users;

use App\Data\Users\ProfileUpdateData;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateProfileAction
{
    public function execute(User $user, ProfileUpdateData $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->person->update($data->personAttributes);

            if ($data->password !== null) {
                $user->forceFill(['password' => $data->password])->save();
            }

            return $user;
        }, DB_TRANSACTION_ATTEMPTS);
    }
}
