<?php

namespace Tests\Feature\Users;

use App\Enums\RoleName;
use App\Enums\SessionEndReason;
use App\Models\User;
use App\Models\UserSession;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class RevokeUserSessionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_revokes_the_sessions_of_a_user_who_can_then_login_again(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin->value);
        $user = User::factory()->create()->assignRole(RoleName::User->value);
        $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])->assertOk();
        Sanctum::actingAs($admin);

        $this->postJson("/api/users/{$user->id}/sessions/revoke")->assertNoContent();

        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(SessionEndReason::Revoked, UserSession::query()->whereBelongsTo($user)->sole()->end_reason);
        $this->assertTrue(Activity::query()->where('event', 'sessions_revoked')->sole()->causer->is($admin));

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])->assertOk();
    }

    public static function forbiddenRevocations(): array
    {
        return [
            'user without permission' => [RoleName::User, RoleName::User],
            'admin on another admin' => [RoleName::Admin, RoleName::Admin],
        ];
    }

    #[DataProvider('forbiddenRevocations')]
    public function test_sessions_cannot_be_revoked_without_permission_or_on_another_admin(RoleName $actorRole, RoleName $targetRole): void
    {
        $target = User::factory()->create()->assignRole($targetRole->value);
        Sanctum::actingAs(User::factory()->create()->assignRole($actorRole->value));

        $this->postJson("/api/users/{$target->id}/sessions/revoke")->assertForbidden();
    }
}
