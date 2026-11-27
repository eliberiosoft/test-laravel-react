<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn () => $this->seedBranch($this->items()), DB_TRANSACTION_ATTEMPTS);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function seedBranch(array $items, ?MenuItem $parent = null): void
    {
        foreach ($items as $item) {
            $menuItem = MenuItem::firstOrNew(['key' => $item['key']]);

            $menuItem->fill([
                'parent_id' => $parent?->getKey(),
                'permission_id' => $this->permissionId($item['permission']),
                'label' => $item['label'],
                'route' => $item['route'],
                'icon' => $item['icon'],
            ]);

            if (! $menuItem->exists) {
                $menuItem->fill([
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]);
            }

            $menuItem->save();

            $this->seedBranch($item['children'] ?? [], $menuItem);
        }
    }

    private function permissionId(?PermissionName $permission): ?int
    {
        return $permission === null
            ? null
            : Permission::findByName($permission->value, PERMISSION_GUARD)->getKey();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function items(): array
    {
        return [
            [
                'key' => 'welcome',
                'label' => 'Inicio',
                'route' => '/welcome',
                'icon' => 'home',
                'permission' => PermissionName::WelcomeView,
                'sort_order' => 10,
            ],
            [
                'key' => 'dashboard',
                'label' => 'Dashboard',
                'route' => '/dashboard',
                'icon' => 'chart',
                'permission' => PermissionName::DashboardView,
                'sort_order' => 20,
            ],
            [
                'key' => 'users',
                'label' => 'Usuarios',
                'route' => null,
                'icon' => 'users',
                'permission' => null,
                'sort_order' => 30,
                'children' => [
                    [
                        'key' => 'users.list',
                        'label' => 'Listado',
                        'route' => '/users',
                        'icon' => 'list',
                        'permission' => PermissionName::UsersView,
                        'sort_order' => 10,
                    ],
                    [
                        'key' => 'users.import',
                        'label' => 'Carga masiva',
                        'route' => '/users/import',
                        'icon' => 'upload',
                        'permission' => PermissionName::UsersImport,
                        'sort_order' => 20,
                    ],
                ],
            ],
            [
                'key' => 'profile',
                'label' => 'Mi perfil',
                'route' => '/profile',
                'icon' => 'user',
                'permission' => PermissionName::ProfileView,
                'sort_order' => 40,
            ],
        ];
    }
}
