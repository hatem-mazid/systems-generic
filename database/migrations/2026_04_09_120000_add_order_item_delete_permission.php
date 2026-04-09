<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');
        $perm = Permission::firstOrCreate(
            ['name' => 'order item delete', 'guard_name' => $guard]
        );

        $roles = Role::query()->where('guard_name', $guard)->whereIn('name', ['admin'])->get();
        foreach ($roles as $role) {
            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');
        Permission::query()
            ->where('name', 'order item delete')
            ->where('guard_name', $guard)
            ->delete();
    }
};
