<?php

namespace Tests\Feature\Auth;

use App\Enums\SessionEndReason;
use App\Models\User;
use App\Models\UserSession;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_request_does_not_reveal_whether_the_account_exists(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $existing = $this->postJson('/api/auth/forgot-password', ['email' => strtoupper($user->email)])->assertOk()->json();
        $unknown = $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@mail.com'])->assertOk()->json();

        $this->assertSame($existing, $unknown);
        Notification::assertSentToTimes($user, ResetPasswordNotification::class, 1);
        Notification::assertCount(1);
    }

    public function test_unknown_email_costs_the_same_hashing_as_an_existing_one(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        Hash::shouldReceive('make')->twice()->andReturn('hashed-value');

        $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@mail.com'])->assertOk();
        $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
    }

    public function test_reset_link_is_only_sent_to_active_accounts(): void
    {
        Notification::fake();
        $blocked = User::factory()->blocked()->create();
        $active = User::factory()->create();

        $toBlocked = $this->postJson('/api/auth/forgot-password', ['email' => $blocked->email])->assertOk()->json();
        $toActive = $this->postJson('/api/auth/forgot-password', ['email' => $active->email])->assertOk()->json();

        $this->assertSame($toActive, $toBlocked);
        Notification::assertNothingSentTo($blocked);
        Notification::assertSentTo($active, ResetPasswordNotification::class, fn (ResetPasswordNotification $notification): bool => $notification instanceof ShouldBeEncrypted);
    }

    public function test_requesting_links_does_not_consume_reset_attempts(): void
    {
        config(['security.password_reset.max_attempts_per_email' => 2]);
        $user = User::factory()->create();

        $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
        $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
        $this->postJson('/api/auth/forgot-password', ['email' => $user->email])->assertTooManyRequests();

        $this->resetPassword($user, Password::broker()->createToken($user), 'Nueva!2026')->assertOk();
    }

    public function test_token_resets_the_password_once_and_revokes_open_sessions(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])->assertOk();
        $token = Password::broker()->createToken($user);

        $user->forceFill(['failed_login_attempts' => 2])->save();

        $this->resetPassword($user, $token, 'Nueva!2026')->assertOk();

        $this->assertTrue(Hash::check('Nueva!2026', $user->refresh()->password));
        $this->assertSame(0, $user->failed_login_attempts);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(SessionEndReason::Revoked, UserSession::query()->whereBelongsTo($user)->sole()->end_reason);

        $this->resetPassword($user, $token, 'Otra!2026')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'invalid_reset_token');
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();

        $this->resetPassword($user, $token, 'Nueva!2026')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'invalid_reset_token');

        $this->assertTrue(Hash::check('Password!1', $user->refresh()->password));
    }

    private function resetPassword(User $user, string $token, string $password): TestResponse
    {
        return $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'token' => $token,
            'password' => $password,
            'password_confirmation' => $password,
        ]);
    }
}
