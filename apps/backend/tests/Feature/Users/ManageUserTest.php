<?php

namespace Tests\Feature\Users;

use App\Enums\RoleName;
use App\Enums\SessionEndReason;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserSession;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ManageUserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole(RoleName::Admin->value);
    }

    public function test_admin_updates_a_common_user_without_changing_its_generated_email(): void
    {
        $user = $this->commonUser();
        $email = $user->email;
        Sanctum::actingAs($this->admin);

        $this->patchJson("/api/users/{$user->id}", ['first_surname' => 'Mendoza', 'username' => 'Renamed01'])
            ->assertOk()
            ->assertJsonPath('data.username', 'Renamed01')
            ->assertJsonPath('data.person.first_surname', 'Mendoza')
            ->assertJsonPath('data.email', $email);
    }

    public static function protectedActions(): array
    {
        return [
            'update' => ['patchJson', ['first_name' => 'Otro']],
            'delete' => ['deleteJson', []],
        ];
    }

    #[DataProvider('protectedActions')]
    public function test_admin_cannot_modify_another_admin(string $method, array $payload): void
    {
        $otherAdmin = User::factory()->create()->assignRole(RoleName::Admin->value);
        Sanctum::actingAs($this->admin);

        $this->{$method}("/api/users/{$otherAdmin->id}", $payload)->assertForbidden();
    }

    public function test_deactivating_a_user_revokes_its_sessions(): void
    {
        $user = $this->commonUser();
        $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])->assertOk();
        Sanctum::actingAs($this->admin);

        $this->patchJson("/api/users/{$user->id}", ['status' => UserStatus::Inactive->value])
            ->assertOk()
            ->assertJsonPath('data.status', UserStatus::Inactive->value);

        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(SessionEndReason::Revoked, UserSession::query()->whereBelongsTo($user)->sole()->end_reason);
    }

    public function test_status_of_a_blocked_user_cannot_be_changed_by_update(): void
    {
        $blocked = User::factory()->blocked()->create()->assignRole(RoleName::User->value);
        Sanctum::actingAs($this->admin);

        $this->patchJson("/api/users/{$blocked->id}", ['status' => UserStatus::Active->value])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status' => 'El usuario está bloqueado. Desbloquéelo antes de cambiar su estado.']);

        $this->assertSame(UserStatus::Blocked, $blocked->refresh()->status);
    }

    public function test_deleting_a_user_is_logical_and_revokes_its_sessions(): void
    {
        $user = $this->commonUser();
        $person = $user->person;
        $user->createToken('api');
        Sanctum::actingAs($this->admin);

        $this->deleteJson("/api/users/{$user->id}")->assertNoContent();

        $this->assertSoftDeleted($user);
        $this->assertSoftDeleted($person);
        $this->assertSame(0, $user->tokens()->count());
        $this->getJson("/api/users/{$user->id}")->assertNotFound();
    }

    private function commonUser(): User
    {
        return User::factory()->create()->assignRole(RoleName::User->value);
    }
}
