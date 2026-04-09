<?php

namespace App\Http\Controllers;

use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCan('roles index');

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10));

        return new RoleCollection($roles);
    }

    public function show(string $id)
    {
        $this->ensureCan('roles edit');

        $role = Role::with('permissions')->find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        return response()->json(new RoleResource($role));
    }

    public function store(Request $request)
    {
        $this->ensureCan('roles create');

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);
        $role->load('permissions');

        return response()->json(new RoleResource($role));
    }

    public function update(Request $request, string $id)
    {
        $this->ensureCan('roles edit');

        $role = Role::with('permissions')->find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        if (array_key_exists('name', $validated)) {
            $role->name = $validated['name'];
            $role->save();
        }

        if (array_key_exists('permissions', $validated)) {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        $role->load('permissions');

        return response()->json(new RoleResource($role));
    }

    public function destroy(string $id)
    {
        $this->ensureCan('roles delete');

        $role = Role::find($id);
        if (! $role) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function permissions()
    {
        $this->ensureAnyCan(['roles create', 'roles edit']);

        return response()->json([
            'items' => Permission::query()
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all(),
        ]);
    }

    private function ensureCan(string $permission): void
    {
        abort_unless(auth()->user()?->can($permission), 403, 'Forbidden');
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function ensureAnyCan(array $permissions): void
    {
        $user = auth()->user();
        $allowed = $user && collect($permissions)->contains(fn ($permission) => $user->can($permission));
        abort_unless($allowed, 403, 'Forbidden');
    }
}
