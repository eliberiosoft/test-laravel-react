<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser(
            role: RoleName::Admin,
            person: [
                'first_name' => 'Ana',
                'middle_name' => 'María',
                'first_surname' => 'Torres',
                'second_surname' => 'Vera',
                'identification' => '0923456781',
                'birth_date' => '1988-03-14',
            ],
            account: [
                'username' => 'Admin001',
                'email' => 'atorresv@mail.com',
                'password' => 'Admin!2026',
            ],
        );

        $this->seedUser(
            role: RoleName::User,
            person: [
                'first_name' => 'Juan',
                'middle_name' => 'Alberto',
                'first_surname' => 'Piguave',
                'second_surname' => 'Loor',
                'identification' => '1203574901',
                'birth_date' => '1992-07-21',
            ],
            account: [
                'username' => 'Jpiguave01',
                'email' => 'jpiguavel@mail.com',
                'password' => 'User!2026',
            ],
        );
    }

    /**
     * @param  array<string, string>  $person
     * @param  array<string, string>  $account
     */
    private function seedUser(RoleName $role, array $person, array $account): void
    {
        DB::transaction(function () use ($role, $person, $account): void {
            $personModel = Person::updateOrCreate(['identification' => $person['identification']], $person);

            $user = User::updateOrCreate(['username' => $account['username']], [
                'person_id' => $personModel->getKey(),
                'email' => $account['email'],
                'password' => $account['password'],
            ]);

            $user->forceFill(['status' => UserStatus::Active, 'failed_login_attempts' => 0, 'locked_at' => null])->save();
            $user->syncRoles([$role->value]);
        }, DB_TRANSACTION_ATTEMPTS);
    }
}
