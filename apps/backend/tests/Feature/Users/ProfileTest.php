<?php

namespace Tests\Feature\Users;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->user = User::factory()->create()->assignRole(RoleName::User->value);
        Sanctum::actingAs($this->user);
    }

    public function test_user_updates_only_its_allowed_data(): void
    {
        $identification = $this->user->person->identification;
        $username = $this->user->username;

        $this->patchJson('/api/profile', [
            'first_name' => 'Nuevo',
            'identification' => '0987654321',
            'username' => 'Hacked001',
        ])->assertOk()->assertJsonPath('data.person.first_name', 'Nuevo');

        $this->user->refresh();
        $this->assertSame($identification, $this->user->person->identification);
        $this->assertSame($username, $this->user->username);
    }

    public function test_changing_the_password_requires_the_current_one(): void
    {
        $this->patchJson('/api/profile', [
            'password' => 'Nueva!2026',
            'password_confirmation' => 'Nueva!2026',
            'current_password' => 'Wrong!2026',
        ])->assertUnprocessable()->assertJsonValidationErrors(['current_password']);

        $this->patchJson('/api/profile', [
            'password' => 'Nueva!2026',
            'password_confirmation' => 'Nueva!2026',
            'current_password' => 'Password!1',
        ])->assertOk();

        $this->assertTrue(Hash::check('Nueva!2026', $this->user->refresh()->password));
    }
}
