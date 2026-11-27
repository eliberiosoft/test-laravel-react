<?php

namespace Tests\Feature\Database;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccountConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public static function duplicatedAttributes(): array
    {
        return [
            'same person' => ['person_id'],
            'same username' => ['username'],
            'same email' => ['email'],
        ];
    }

    #[DataProvider('duplicatedAttributes')]
    public function test_database_rejects_duplicated_accounts(string $attribute): void
    {
        $existing = User::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);

        User::factory()->create([
            'person_id' => $attribute === 'person_id' ? $existing->person_id : Person::factory(),
            $attribute => $existing->getAttribute($attribute),
        ]);
    }

    public static function invalidValues(): array
    {
        return [
            'unknown user status' => ['users', 'status', 'deleted'],
            'malformed identification' => ['persons', 'identification', '12345ABCDE'],
        ];
    }

    #[DataProvider('invalidValues')]
    public function test_check_constraints_reject_invalid_values_written_outside_the_application(string $table, string $column, string $value): void
    {
        $user = User::factory()->create();
        $id = $table === 'users' ? $user->id : $user->person_id;

        $this->expectException(QueryException::class);
        $this->expectExceptionMessageMatches('/chk_/');

        DB::table($table)->where('id', $id)->update([$column => $value]);
    }
}
