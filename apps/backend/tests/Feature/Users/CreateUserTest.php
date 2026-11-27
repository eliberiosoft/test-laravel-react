<?php

namespace Tests\Feature\Users;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::Admin->value));
    }

    public function test_admin_creates_a_common_user_with_a_generated_email(): void
    {
        $this->postJson('/api/users', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.email', 'jpiguavel@mail.com')
            ->assertJsonPath('data.roles', [RoleName::User->value])
            ->assertJsonPath('data.person.full_name', 'Juan Alberto Piguave Loor');

        $user = User::query()->where('username', 'Jpiguave01')->sole();
        $this->assertTrue(Hash::check('Clave!2026', $user->password));
    }

    public function test_generated_email_takes_the_next_free_numeric_suffix(): void
    {
        User::factory()->create(['email' => 'jpiguavel@mail.com']);
        User::factory()->create(['email' => 'jpiguavel3@mail.com']);
        User::factory()->create(['email' => 'jpiguavela@mail.com']);

        $this->postJson('/api/users', $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.email', 'jpiguavel4@mail.com');
    }

    public static function invalidFields(): array
    {
        return [
            'username without uppercase' => ['username', 'jpiguave01'],
            'password with spaces' => ['password', 'Clave !2026'],
            'password without symbol' => ['password', 'Clave2026'],
            'identification with four repeated digits' => ['identification', '1200007891'],
            'name without letters' => ['first_name', '-'],
        ];
    }

    #[DataProvider('invalidFields')]
    public function test_business_rules_are_validated(string $field, string $value): void
    {
        $this->postJson('/api/users', $this->payload([$field => $value]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field]);

        $this->assertDatabaseMissing('persons', ['identification' => $this->payload()['identification']]);
    }

    public function test_common_user_cannot_create_users(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::User->value));

        $this->postJson('/api/users', $this->payload())->assertForbidden();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Juan',
            'middle_name' => 'Alberto',
            'first_surname' => 'Piguave',
            'second_surname' => 'Loor',
            'identification' => '1203574901',
            'birth_date' => '1992-07-21',
            'username' => 'Jpiguave01',
            'password' => 'Clave!2026',
        ], $overrides);
    }
}
