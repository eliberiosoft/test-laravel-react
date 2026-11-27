<?php

namespace Database\Seeders;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(PermissionRegistrar $registrar): void
    {
        DB::transaction(function () use ($registrar): void {
            foreach (PermissionName::cases() as $permission) {
                Permission::findOrCreate($permission->value, PERMISSION_GUARD);
            }

            $registrar->forgetCachedPermissions();

            foreach (RoleName::cases() as $role) {
                Role::findOrCreate($role->value, PERMISSION_GUARD)->syncPermissions(
                    array_map(fn (PermissionName $permission): string => $permission->value, $role->permissions())
                );
            }
        }, DB_TRANSACTION_ATTEMPTS);

        $registrar->forgetCachedPermissions();
    }
}
