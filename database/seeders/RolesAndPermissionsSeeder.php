<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'users index']);
        Permission::create(['name' => 'users create']);
        Permission::create(['name' => 'users edit']);
        Permission::create(['name' => 'users delete']);

        Permission::create(['name' => 'roles index']);
        Permission::create(['name' => 'roles create']);
        Permission::create(['name' => 'roles edit']);
        Permission::create(['name' => 'roles delete']);

        Permission::create(['name' => 'products index']);
        Permission::create(['name' => 'products create']);
        Permission::create(['name' => 'products edit']);
        Permission::create(['name' => 'products delete']);

        Permission::create(['name' => 'categories index']);
        Permission::create(['name' => 'categories create']);
        Permission::create(['name' => 'categories edit']);
        Permission::create(['name' => 'categories delete']);

        Permission::create(['name' => 'unit-groups index']);
        Permission::create(['name' => 'unit-groups create']);
        Permission::create(['name' => 'unit-groups edit']);
        Permission::create(['name' => 'unit-groups delete']);

        Permission::create(['name' => 'units index']);
        Permission::create(['name' => 'units create']);
        Permission::create(['name' => 'units edit']);
        Permission::create(['name' => 'units delete']);

        Permission::create(['name' => 'order index']);
        Permission::create(['name' => 'order create']);
        Permission::create(['name' => 'order close']);
        Permission::create(['name' => 'order edit']);
        Permission::create(['name' => 'order item delete']);
        Permission::create(['name' => 'order delete']);

        Permission::create(['name' => 'view reports']);

        Permission::create(['name' => 'expenses index']);
        Permission::create(['name' => 'expenses create']);
        Permission::create(['name' => 'expenses edit']);

        // Roles
        $admin = Role::create(['name' => 'admin']);
        $accounting = Role::create(['name' => 'accounting']);
        $waiter = Role::create(['name' => 'waiter']);

        // Assign permissions

        $admin->givePermissionTo(Permission::all());

        $accounting->givePermissionTo([
            'view reports',
            'expenses index',
            'expenses create',
            'expenses edit',
        ]);

        // $waiter->givePermissionTo([
        //     'create order',
        //     'close order'
        // ]);
    }
}
