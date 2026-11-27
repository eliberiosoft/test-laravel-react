<?php

namespace Tests\Feature\Users;

use App\Actions\Users\CreateUserAction;
use App\Data\Users\NewUserData;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\Concerns\RunsConcurrently;
use Tests\TestCase;

class ConcurrentUserCreationTest extends TestCase
{
    use DatabaseTruncation, RunsConcurrently;

    protected function tearDown(): void
    {
        $this->truncateDatabaseTables();

        parent::tearDown();
    }

    public function test_simultaneous_users_with_the_same_name_get_unique_emails(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $emails = $this->runConcurrently(5, fn (int $index): string => app(CreateUserAction::class)->execute(new NewUserData(
            firstName: 'Juan',
            middleName: null,
            firstSurname: 'Piguave',
            secondSurname: 'Loor',
            identification: "120357490{$index}",
            birthDate: null,
            username: "Jpiguave0{$index}",
            password: 'Clave!2026',
        ))->email);

        sort($emails);

        $this->assertSame([
            'jpiguavel1@mail.com',
            'jpiguavel2@mail.com',
            'jpiguavel3@mail.com',
            'jpiguavel4@mail.com',
            'jpiguavel@mail.com',
        ], $emails);
    }
}
