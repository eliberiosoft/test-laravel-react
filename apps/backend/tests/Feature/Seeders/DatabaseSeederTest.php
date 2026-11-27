<?php

namespace Tests\Feature\Seeders;

use App\Enums\RoleName;
use App\Models\MenuItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seed_creates_roles_and_menu_but_no_demo_users(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');

        $this->app->make(DatabaseSeeder::class)->setContainer($this->app)->__invoke();

        $this->assertSame(count(RoleName::cases()), Role::count());
        $this->assertSame(6, MenuItem::count());
        $this->assertSame(0, User::count());
    }
}
