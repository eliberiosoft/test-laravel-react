<?php

namespace Tests\Feature\Auth;

use App\Enums\LoginResult;
use App\Enums\SessionEndReason;
use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_second_login_is_rejected_while_a_session_is_active(): void
    {
        $user = User::factory()->create();
        $this->loginAs($user);

        $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])
            ->assertConflict()
            ->assertJsonPath('code', 'active_session');

        $this->assertSame(1, UserSession::query()->whereBelongsTo($user)->count());
        $this->assertDatabaseHas('login_attempts', ['user_id' => $user->id, 'result' => LoginResult::ActiveSession->value]);
    }

    public static function staleSessions(): array
    {
        return [
            'expired and pruned' => [true, SessionEndReason::Expired],
            'revoked' => [false, SessionEndReason::Revoked],
        ];
    }

    #[DataProvider('staleSessions')]
    public function test_stale_session_is_closed_with_its_real_reason_on_next_login(bool $expired, SessionEndReason $reason): void
    {
        $user = User::factory()->create();
        $this->loginAs($user);

        if ($expired) {
            $this->travel(config('sanctum.expiration') + 1)->minutes();
        }

        $user->tokens()->delete();

        $this->loginAs($user);

        $previous = UserSession::query()->whereBelongsTo($user)->oldest('id')->first();
        $this->assertSame($reason, $previous->end_reason);
        $this->assertNotNull($previous->ended_at);
    }

    public function test_logout_ends_the_session_and_allows_a_new_login(): void
    {
        $user = User::factory()->create();
        $token = $this->loginAs($user);

        $this->withToken($token)->postJson('/api/auth/logout')->assertNoContent();

        $this->assertSame(SessionEndReason::Logout, UserSession::query()->whereBelongsTo($user)->sole()->end_reason);
        $this->assertSame(0, $user->tokens()->count());

        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/auth/me')->assertUnauthorized();
        $this->loginAs($user);
    }

    public function test_token_is_valid_only_for_the_configured_lifetime(): void
    {
        $token = $this->loginAs(User::factory()->create());

        $this->travel(config('sanctum.expiration') - 1)->minutes();
        $this->withToken($token)->getJson('/api/auth/me')->assertOk();

        $this->app['auth']->forgetGuards();
        $this->travel(2)->minutes();
        $this->withToken($token)->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'session_expired');
    }

    public function test_token_of_a_user_blocked_after_login_is_revoked(): void
    {
        $user = User::factory()->create();
        $token = $this->loginAs($user);

        $user->forceFill(['status' => UserStatus::Blocked, 'locked_at' => now()])->save();

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertStatus(423)
            ->assertJsonPath('code', 'account_blocked');

        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(SessionEndReason::Revoked, UserSession::query()->whereBelongsTo($user)->sole()->end_reason);
    }

    private function loginAs(User $user): string
    {
        return $this->postJson('/api/auth/login', ['login' => $user->username, 'password' => 'Password!1'])
            ->assertOk()
            ->json('data.token');
    }
}
