<?php

namespace App\Actions\Users;

use App\Data\Users\NewUserData;
use App\Enums\RoleName;
use App\Models\Person;
use App\Models\User;
use App\Services\Users\EmailAddressService;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateUserAction
{
    public function __construct(private readonly EmailAddressService $emails) {}

    public function execute(NewUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $person = Person::create($data->personAttributes());

            $user = $this->createAccount($person, $data);
            $user->assignRole(RoleName::User->value);

            return $user;
        }, DB_TRANSACTION_ATTEMPTS);
    }

    private function createAccount(Person $person, NewUserData $data): User
    {
        $localPart = $this->emails->localPart($data->firstName, $data->firstSurname, $data->secondSurname);

        return retry(
            EMAIL_GENERATION_ATTEMPTS,
            fn (): User => $person->user()->create([
                'username' => $data->username,
                'email' => $this->emails->nextAvailable($localPart),
                'password' => $data->password,
            ]),
            when: fn (Throwable $exception): bool => $this->emails->isConflict($exception),
        );
    }
}
