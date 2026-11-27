<?php

namespace Tests\Feature\Users;

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        Sanctum::actingAs(User::factory()->create()->assignRole(RoleName::Admin->value));
    }

    public function test_admin_lists_users_filtered_sorted_and_paginated_on_the_server(): void
    {
        foreach (['Charlie01', 'Alpha0001', 'Bravo0001'] as $username) {
            User::factory()->inactive()->create(['username' => $username]);
        }
        $target = User::factory()->create();

        $this->getJson('/api/users?filter[status]=inactive&sort=username&per_page=2')
            ->assertOk()
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.*.username', ['Alpha0001', 'Bravo0001']);

        $this->getJson('/api/users?filter[search]='.$target->person->identification)
            ->assertOk()
            ->assertJsonPath('data.*.id', [$target->id]);
    }

    public function test_search_needs_three_characters_and_is_taken_literally(): void
    {
        $this->getJson('/api/users?filter[search]=ab')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['filter.search' => 'La búsqueda debe tener al menos 3 caracteres.']);

        $this->getJson('/api/users?filter[search]=pig,loor')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $literal = User::factory()->create(['username' => 'TrueUser1']);
        User::factory()->create(['username' => 'Other0001']);

        $this->getJson('/api/users?filter[search]=true')
            ->assertOk()
            ->assertJsonPath('data.*.id', [$literal->id]);

        $this->getJson('/api/users?filter[role]=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['filter.role']);
    }

    public function test_listing_does_not_run_one_query_per_user(): void
    {
        User::factory()->count(2)->create();
        $this->getJson('/api/users')->assertOk();
        $fewUsers = $this->countQueries(fn () => $this->getJson('/api/users?per_page=50')->assertOk());

        User::factory()->count(8)->create();
        $manyUsers = $this->countQueries(fn () => $this->getJson('/api/users?per_page=50')->assertOk());

        $this->assertSame($fewUsers, $manyUsers);
    }

    private function countQueries(callable $callback): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $callback();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }
}
