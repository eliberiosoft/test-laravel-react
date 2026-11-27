<?php

namespace Tests\Feature\Auth;

use App\Enums\LoginResult;
use App\Enums\UserStatus;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Notifications\AccountLockedNotification;
use Closure;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const string PASSWORD = 'Password!1';

    public static function loginIdentifiers(): array
    {
        return [
            'username' => [fn (User $user): string => $user->username],
            'email in uppercase' => [fn (User $user): string => strtoupper($user->email)],
        ];
    }

    #[DataProvider('loginIdentifiers')]
    public function test_user_can_login_with_username_or_email(Closure $identifier): void
    {
        $user = User::factory()->create();

        $token = $this->login($identifier($user))
            ->assertOk()
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['data' => [
                'token',
                'expires_at',
                'user' => ['username', 'email', 'status', 'person' => ['full_name'], 'roles', 'permissions'],
            ]])
            ->json('data.token');

        $this->assertDatabaseHas('user_sessions', ['user_id' => $user->id, 'ended_at' => null]);
        $this->assertDatabaseHas('login_attempts', ['user_id' => $user->id, 'result' => LoginResult::Success->value]);
        $this->withToken($token)->getJson('/api/auth/me')->assertOk();
    }

    public function test_wrong_password_and_unknown_account_get_identical_responses(): void
    {
        $user = User::factory()->create();

        $wrongPassword = $this->login($user->username, 'WrongPass!9')->assertUnauthorized()->json();
        $unknownAccount = $this->login('NoSuchUser1', 'WrongPass!9')->assertUnauthorized()->json();

        $this->assertSame($wrongPassword, $unknownAccount);
        $this->assertSame('invalid_credentials', $wrongPassword['code']);
        $this->assertSame(1, $user->refresh()->failed_login_attempts);
        $this->assertDatabaseHas('login_attempts', ['user_id' => null, 'identifier' => 'nosuchuser1']);
    }

    public function test_account_is_blocked_after_three_consecutive_failures_and_the_owner_is_notified(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        foreach (range(1, 3) as $ignored) {
            $this->login($user->username, 'WrongPass!9')->assertUnauthorized();
        }

        $user->refresh();
        $this->assertSame(UserStatus::Blocked, $user->status);
        $this->assertNotNull($user->locked_at);
        Notification::assertSentToTimes($user, AccountLockedNotification::class, 1);
        $this->assertDatabaseMissing('user_sessions', ['user_id' => $user->id]);
    }

    public static function deniedAccounts(): array
    {
        return [
            'blocked' => ['blocked', LoginResult::AccountBlocked],
            'inactive' => ['inactive', LoginResult::AccountInactive],
        ];
    }

    #[DataProvider('deniedAccounts')]
    public function test_denied_account_gets_the_generic_response_even_with_the_right_password(string $state, LoginResult $recorded): void
    {
        $user = User::factory()->{$state}()->create();

        $rightPassword = $this->login($user->username)->assertUnauthorized()->json();
        $wrongPassword = $this->login($user->username, 'WrongPass!9')->assertUnauthorized()->json();

        $this->assertSame($rightPassword, $wrongPassword);
        $this->assertSame('invalid_credentials', $rightPassword['code']);
        $this->assertDatabaseHas('login_attempts', ['user_id' => $user->id, 'result' => $recorded->value]);
        $this->assertDatabaseMissing('user_sessions', ['user_id' => $user->id]);
    }

    public function test_successful_login_resets_the_failure_counter(): void
    {
        $user = User::factory()->create();

        $this->login($user->username, 'WrongPass!9');
        $this->login($user->username, 'WrongPass!9');
        $this->login($user->username)->assertOk();

        $this->assertSame(0, $user->refresh()->failed_login_attempts);
    }

    public function test_login_rejects_invalid_input(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login', 'password']);

        $this->postJson('/api/auth/login', ['login' => ['x'], 'password' => ['y']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['login', 'password']);
    }

    public function test_passwords_are_never_stored_in_audit_tables(): void
    {
        $user = User::factory()->create();
        $originalHash = $user->password;
        Hash::driver('bcrypt')->setRounds(5);

        $this->login($user->username, 'Leaked!Secret9')->assertUnauthorized();
        $this->login($user->username)->assertOk();

        $rehashed = $user->refresh()->password;
        $this->assertNotSame($originalHash, $rehashed);

        $audit = json_encode([
            LoginAttempt::all()->toArray(),
            DB::table('activity_log')->get()->toArray(),
        ]);

        foreach (['Leaked!Secret9', self::PASSWORD, $originalHash, $rehashed] as $secret) {
            $this->assertStringNotContainsString($secret, $audit);
        }
    }

    public function test_unknown_account_costs_the_same_hashing_as_a_wrong_password(): void
    {
        $user = User::factory()->create();
        $hasher = $this->spyOnHasher();
        $this->login('Warmup001', 'WrongPass!9');
        $this->assertTrue(Cache::has(LOGIN_DUMMY_HASH_CACHE_KEY));

        $hasher->reset();
        $this->login('NoSuchUser1', 'WrongPass!9')->assertUnauthorized();
        $unknownAccount = $hasher->calls;

        $hasher->reset();
        $this->login($user->username, 'WrongPass!9')->assertUnauthorized();

        $this->assertSame(['make' => 0, 'check' => 1], $unknownAccount);
        $this->assertSame($unknownAccount, $hasher->calls);
    }

    public function test_identifier_is_rate_limited_across_ips(): void
    {
        config(['security.login.max_attempts_per_identifier' => 3]);

        foreach (range(1, 3) as $i) {
            $this->login('Someone001', 'WrongPass!9', "10.0.1.{$i}")->assertUnauthorized();
        }

        $this->login('Someone001', 'WrongPass!9', '10.0.1.99')->assertTooManyRequests();
    }

    public function test_ip_is_rate_limited_across_identifiers(): void
    {
        config(['security.login.max_attempts_per_ip' => 3]);

        foreach (range(1, 3) as $i) {
            $this->login("Someone00{$i}", 'WrongPass!9', '10.0.2.1')->assertUnauthorized();
        }

        $this->login('Someone009', 'WrongPass!9', '10.0.2.1')->assertTooManyRequests();
        $this->login('Someone009', 'WrongPass!9', '10.0.2.2')->assertUnauthorized();
    }

    public function test_failed_logins_take_a_minimum_time_and_successful_ones_do_not(): void
    {
        config(['security.login.min_failure_response_ms' => 300]);
        $user = User::factory()->create();

        $this->assertGreaterThanOrEqual(300, $this->elapsedMs(fn () => $this->login('NoSuchUser1', 'WrongPass!9')));
        $this->assertLessThan(300, $this->elapsedMs(fn () => $this->login($user->username)->assertOk()));
    }

    private function login(string $login, string $password = self::PASSWORD, string $ip = '127.0.0.1'): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson('/api/auth/login', ['login' => $login, 'password' => $password]);
    }

    private function spyOnHasher(): object
    {
        $spy = new class(Hash::driver()) implements Hasher
        {
            public array $calls = ['make' => 0, 'check' => 0];

            public function __construct(private readonly Hasher $hasher) {}

            public function reset(): void
            {
                $this->calls = ['make' => 0, 'check' => 0];
            }

            public function info($hashedValue): array
            {
                return $this->hasher->info($hashedValue);
            }

            public function make($value, array $options = []): string
            {
                $this->calls['make']++;

                return $this->hasher->make($value, $options);
            }

            public function check($value, $hashedValue, array $options = []): bool
            {
                $this->calls['check']++;

                return $this->hasher->check($value, $hashedValue, $options);
            }

            public function needsRehash($hashedValue, array $options = []): bool
            {
                return $this->hasher->needsRehash($hashedValue, $options);
            }
        };

        $this->app->instance(Hasher::class, $spy);

        return $spy;
    }

    private function elapsedMs(callable $callback): float
    {
        $start = hrtime(true);
        $callback();

        return (hrtime(true) - $start) / 1_000_000;
    }
}
