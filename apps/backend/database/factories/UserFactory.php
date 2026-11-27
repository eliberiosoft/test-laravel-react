<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'username' => 'User'.fake()->unique()->numerify('####'),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('Password!1'),
            'status' => UserStatus::Active,
            'failed_login_attempts' => 0,
            'locked_at' => null,
        ];
    }

    public function blocked(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatus::Blocked,
            'failed_login_attempts' => 3,
            'locked_at' => now(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => UserStatus::Inactive,
        ]);
    }
}
