<?php

namespace Tests\Feature;

use App\Enums\RoleName;
use App\Enums\SessionEndReason;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class WelcomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_shows_the_previous_session_and_failures_since_then(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $user = User::factory()->create()->assignRole(RoleName::User->value);

        $firstToken = $this->login($user->username, 'Password!1')->json('data.token');
        $this->withToken($firstToken)->postJson('/api/auth/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();

        $this->travel(5)->minutes();
        $this->login($user->username, 'WrongPass!9');
        $this->login($user->username, 'WrongPass!9');
        $token = $this->login($user->username, 'Password!1')->json('data.token');

        $this->withToken($token)->getJson('/api/welcome')
            ->assertOk()
            ->assertJsonPath('data.user.username', $user->username)
            ->assertJsonPath('data.last_session.end_reason', SessionEndReason::Logout->value)
            ->assertJsonPath('data.failed_attempts.since_last_session', 2)
            ->assertJsonStructure(['data' => ['last_session' => ['started_at', 'ended_at', 'ip_address'], 'failed_attempts' => ['last_attempt_at']]]);
    }

    private function login(string $login, string $password): TestResponse
    {
        return $this->postJson('/api/auth/login', ['login' => $login, 'password' => $password]);
    }
}
