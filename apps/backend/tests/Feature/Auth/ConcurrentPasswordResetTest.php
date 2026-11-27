<?php

namespace Tests\Feature\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Data\Auth\PasswordResetData;
use App\Exceptions\Auth\InvalidPasswordResetTokenException;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Password;
use Tests\Concerns\RunsConcurrently;
use Tests\TestCase;

class ConcurrentPasswordResetTest extends TestCase
{
    use DatabaseTruncation, RunsConcurrently;

    protected function tearDown(): void
    {
        $this->truncateDatabaseTables();

        parent::tearDown();
    }

    public function test_a_reset_token_can_only_be_used_once_even_with_simultaneous_requests(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $results = $this->runConcurrently(4, function (int $index) use ($user, $token): string {
            try {
                app(ResetPasswordAction::class)->execute(new PasswordResetData($user->email, $token, "Nueva!202{$index}"));

                return 'reset';
            } catch (InvalidPasswordResetTokenException) {
                return 'invalid';
            }
        });

        $counts = array_count_values($results);
        ksort($counts);

        $this->assertSame(['invalid' => 3, 'reset' => 1], $counts);
    }
}
