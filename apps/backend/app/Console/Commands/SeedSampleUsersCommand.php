<?php

namespace App\Console\Commands;

use App\Actions\Users\CreateUserAction;
use App\Data\Users\NewUserData;
use App\Enums\UserStatus;
use App\Models\Person;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedSampleUsersCommand extends Command
{
    protected $signature = 'users:sample {count='.SAMPLE_USERS_DEFAULT.' : Cantidad de usuarios a crear}';

    protected $description = 'Crea usuarios de ejemplo con estados variados (solo en local y testing)';

    public function handle(CreateUserAction $createUser): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->components->error('Solo se permite en los entornos local y testing.');

            return self::FAILURE;
        }

        $count = max(1, (int) $this->argument('count'));
        $lastNumber = $this->lastSampleNumber();

        $this->withProgressBar(range(1, $count), function (int $index) use ($createUser, $lastNumber): void {
            $user = $createUser->execute($this->sampleData($lastNumber + $index));
            $this->applyStatus($user, $lastNumber + $index);
        });

        $this->newLine(2);
        $this->components->info(sprintf('Se crearon %d usuarios de ejemplo (%s…). Contraseña: %s', $count, SAMPLE_USERNAME_PREFIX, SAMPLE_USER_PASSWORD));

        return self::SUCCESS;
    }

    private function lastSampleNumber(): int
    {
        return (int) User::withTrashed()
            ->where('username', 'like', SAMPLE_USERNAME_PREFIX.'%')
            ->pluck('username')
            ->map(fn (string $username): int => (int) Str::after($username, SAMPLE_USERNAME_PREFIX))
            ->max();
    }

    private function sampleData(int $number): NewUserData
    {
        $person = $this->uniquePerson();

        return new NewUserData(
            firstName: $person['first_name'],
            middleName: $person['middle_name'],
            firstSurname: $person['first_surname'],
            secondSurname: $person['second_surname'],
            identification: $person['identification'],
            birthDate: $person['birth_date'],
            username: sprintf('%s%04d', SAMPLE_USERNAME_PREFIX, $number),
            password: SAMPLE_USER_PASSWORD,
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function uniquePerson(): array
    {
        do {
            $person = Person::factory()->raw();
        } while (Person::withTrashed()->where('identification', $person['identification'])->exists());

        return $person;
    }

    private function applyStatus(User $user, int $number): void
    {
        $attributes = match ($number % SAMPLE_STATUS_CYCLE) {
            SAMPLE_BLOCKED_POSITION => [
                'status' => UserStatus::Blocked,
                'failed_login_attempts' => (int) config('security.lockout.max_failed_attempts'),
                'locked_at' => now(),
            ],
            SAMPLE_INACTIVE_POSITION => ['status' => UserStatus::Inactive],
            default => [],
        };

        if ($attributes !== []) {
            $user->forceFill($attributes)->save();
        }
    }
}
