<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'users index']);
        Permission::create(['name' => 'users create']);
        Permission::create(['name' => 'users edit']);
        Permission::create(['name' => 'users delete']);

        Permission::create(['name' => 'products index']);
        Permission::create(['name' => 'products create']);
        Permission::create(['name' => 'products edit']);
        Permission::create(['name' => 'products delete']);

        Permission::create(['name' => 'units index']);
        Permission::create(['name' => 'units create']);
        Permission::create(['name' => 'units edit']);
        Permission::create(['name' => 'units delete']);

        Permission::create(['name' => 'order index']);
        Permission::create(['name' => 'order create']);
        Permission::create(['name' => 'order close']);
        Permission::create(['name' => 'order edit']);
        Permission::create(['name' => 'order delete']);

        Permission::create(['name' => 'view reports']);

        // Roles
        $admin = Role::create(['name' => 'admin']);
        $accounting = Role::create(['name' => 'accounting']);
        $waiter = Role::create(['name' => 'waiter']);

        // Assign permissions

        $admin->givePermissionTo(Permission::all());

        // $accounting->givePermissionTo([
        //     'view reports'
        // ]);

        // $waiter->givePermissionTo([
        //     'create order',
        //     'close order'
        // ]);
    }
}
