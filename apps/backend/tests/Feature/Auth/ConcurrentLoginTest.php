<?php

namespace Tests\Feature\Auth;

use App\Actions\Auth\LoginAction;
use App\Data\Auth\LoginCredentials;
use App\Enums\UserStatus;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\Concerns\RunsConcurrently;
use Tests\TestCase;

class ConcurrentLoginTest extends TestCase
{
    use DatabaseTruncation, RunsConcurrently;

    protected function tearDown(): void
    {
        $this->truncateDatabaseTables();

        parent::tearDown();
    }

    public function test_simultaneous_logins_open_a_single_session(): void
    {
        $user = User::factory()->create(['username' => 'Concurrent1']);

        $results = $this->runConcurrently(6, fn (): string => $this->attemptLogin('Concurrent1', 'Password!1'));

        $this->assertSame(['active_session' => 5, 'success' => 1], $this->tally($results));
        $this->assertSame(1, UserSession::query()->whereBelongsTo($user)->count());
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_simultaneous_failures_are_counted_without_losing_updates(): void
    {
        config(['security.lockout.max_failed_attempts' => 100]);
        $user = User::factory()->create(['username' => 'Concurrent2']);

        $results = $this->runConcurrently(6, fn (): string => $this->attemptLogin('Concurrent2', 'WrongPass!9'));

        $this->assertSame(['invalid_credentials' => 6], $this->tally($results));

        $user->refresh();
        $this->assertSame(6, $user->failed_login_attempts);
        $this->assertSame(UserStatus::Active, $user->status);
    }

    private function attemptLogin(string $login, string $password): string
    {
        try {
            app(LoginAction::class)->execute(new LoginCredentials($login, $password, '127.0.0.1', 'phpunit'));

            return 'success';
        } catch (ApiException $exception) {
            return $exception->errorCode();
        }
    }

    /**
     * @param  list<string>  $results
     * @return array<string, int>
     */
    private function tally(array $results): array
    {
        $counts = array_count_values($results);
        ksort($counts);

        return $counts;
    }
}
