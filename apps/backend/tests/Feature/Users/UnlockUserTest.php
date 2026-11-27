<?php

namespace Tests\Feature\Users;

use App\Enums\RoleName;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class UnlockUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_unlocks_a_blocked_user_and_the_action_is_audited(): void
    {
        $admin = $this->userWithRole(RoleName::Admin);
        $blocked = User::factory()->blocked()->create();
        Sanctum::actingAs($admin);

        $this->postJson("/api/users/{$blocked->id}/unlock")
            ->assertOk()
            ->assertJsonPath('data.status', UserStatus::Active->value);

        $blocked->refresh();
        $this->assertSame(0, $blocked->failed_login_attempts);
        $this->assertNull($blocked->locked_at);

        $activity = Activity::query()
            ->where('log_name', 'users')
            ->where('event', 'updated')
            ->where('subject_id', $blocked->id)
            ->sole();
        $this->assertTrue($activity->causer->is($admin));
        $this->assertSame('blocked', $activity->attribute_changes['old']['status']);

        $this->app['auth']->forgetGuards();
        $this->postJson('/api/auth/login', ['login' => $blocked->username, 'password' => 'Password!1'])->assertOk();
    }

    public static function forbiddenActors(): array
    {
        return [
            'user without permission' => [RoleName::User, false],
            'admin on itself' => [RoleName::Admin, true],
        ];
    }

    #[DataProvider('forbiddenActors')]
    public function test_unlock_is_forbidden(RoleName $role, bool $targetsItself): void
    {
        $actor = $this->userWithRole($role);
        $target = $targetsItself ? $actor : User::factory()->blocked()->create();
        Sanctum::actingAs($actor);

        $this->postJson("/api/users/{$target->id}/unlock")->assertForbidden();
    }

    public function test_unlocking_a_user_that_is_not_blocked_is_a_conflict(): void
    {
        $active = User::factory()->create();
        Sanctum::actingAs($this->userWithRole(RoleName::Admin));

        $this->postJson("/api/users/{$active->id}/unlock")
            ->assertConflict()
            ->assertJsonPath('code', 'user_not_blocked');
    }

    private function userWithRole(RoleName $role): User
    {
        return User::factory()->create()->assignRole($role->value);
    }
}
