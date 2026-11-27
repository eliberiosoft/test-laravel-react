<?php

namespace Tests\Feature\Auth;

use App\Data\Auth\IssuedSession;
use App\Data\Auth\LoginCredentials;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\UserSession;
use App\Services\Auth\SessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class LoginTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_failure_in_the_middle_of_login_rolls_back_every_write(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['failed_login_attempts' => 2])->save();

        $this->app->bind(SessionService::class, fn () => new class extends SessionService
        {
            public function open(User $user, LoginCredentials $credentials): IssuedSession
            {
                parent::open($user, $credentials);

                throw new RuntimeException('Simulated failure after issuing the session.');
            }
        });

        $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])
            ->assertServerError();

        $this->assertSame(2, $user->refresh()->failed_login_attempts);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(0, UserSession::count());
        $this->assertSame(0, LoginAttempt::count());
    }
}
