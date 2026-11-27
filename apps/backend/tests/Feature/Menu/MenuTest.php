<?php

namespace Tests\Feature\Menu;

use App\Enums\RoleName;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\MenuSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolesAndPermissionsSeeder::class, MenuSeeder::class]);
    }

    public function test_admin_receives_the_full_menu_tree_in_order(): void
    {
        Sanctum::actingAs($this->userWithRole(RoleName::Admin));

        $this->getJson('/api/menu')
            ->assertOk()
            ->assertJsonPath('data.*.key', ['welcome', 'dashboard', 'users', 'profile'])
            ->assertJsonPath('data.2.children.*.key', ['users.list', 'users.import']);
    }

    public function test_common_user_only_receives_items_allowed_by_its_role(): void
    {
        Sanctum::actingAs($this->userWithRole(RoleName::User));

        $this->getJson('/api/menu')
            ->assertOk()
            ->assertJsonPath('data.*.key', ['welcome', 'profile']);
    }

    public function test_group_visibility_depends_on_its_visible_children(): void
    {
        Sanctum::actingAs(User::factory()->create()->givePermissionTo('users.import'));

        $this->getJson('/api/menu')
            ->assertOk()
            ->assertJsonPath('data.*.key', ['users'])
            ->assertJsonPath('data.0.children.*.key', ['users.import']);

        MenuItem::query()->where('key', 'users.import')->update(['is_active' => false]);

        $this->getJson('/api/menu')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_menu_query_count_does_not_grow_with_the_number_of_items(): void
    {
        Sanctum::actingAs($this->userWithRole(RoleName::Admin));
        $this->getJson('/api/menu')->assertOk();

        $baseline = $this->countQueries(fn () => $this->getJson('/api/menu')->assertOk());

        $parent = MenuItem::query()->where('key', 'users')->sole();
        $permission = Permission::findByName('users.view', PERMISSION_GUARD);
        foreach (range(1, 5) as $i) {
            MenuItem::create([
                'parent_id' => $parent->id,
                'permission_id' => $permission->id,
                'key' => "users.extra.{$i}",
                'label' => "Extra {$i}",
                'route' => "/users/extra/{$i}",
                'sort_order' => 100 + $i,
            ]);
        }

        $this->assertSame($baseline, $this->countQueries(fn () => $this->getJson('/api/menu')->assertOk()));
    }

    private function userWithRole(RoleName $role): User
    {
        return User::factory()->create()->assignRole($role->value);
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
