<?php

namespace Tests\Feature;

use App\Enums\LoginResult;
use App\Enums\RoleName;
use App\Models\LoginAttempt;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_receives_metrics_calculated_on_the_server(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin->value);
        User::factory()->count(2)->blocked()->create();
        User::factory()->inactive()->create();
        $active = User::factory()->create();
        $this->postJson('/api/auth/login', ['login' => $active->username, 'password' => 'Password!1'])->assertOk();

        $this->recordFailure(null, now()->subHours(2));
        $this->recordFailure($active->id, now()->subDays(3));
        $this->recordFailure($active->id, now()->subDays(10));
        $this->recordFailure($active->id, now()->subHour(), LoginResult::ActiveSession);

        Sanctum::actingAs($admin);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertExactJson(['data' => [
                'accounts' => ['total' => 5, 'active' => 2, 'blocked' => 2, 'inactive' => 1],
                'sessions' => ['active' => 1, 'started_last_24_hours' => 1],
                'failed_attempts' => ['last_24_hours' => 1, 'last_7_days' => 2, 'unknown_accounts_last_7_days' => 1],
            ]]);
    }

    public function test_common_user_cannot_see_the_dashboard(): void
    {
        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::User->value));

        $this->getJson('/api/dashboard')->assertForbidden();
    }

    private function recordFailure(?int $userId, \DateTimeInterface $at, LoginResult $result = LoginResult::InvalidCredentials): void
    {
        LoginAttempt::create([
            'user_id' => $userId,
            'identifier' => 'someone',
            'result' => $result,
            'attempted_at' => $at,
        ]);
    }
}
