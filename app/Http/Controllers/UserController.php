<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::with('roles')->paginate($request->integer('per_page', 10));

        return new UserCollection($users);
    }

    public function show(string $id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return response()->json(['message' => __('User not found', $_SESSION['locale'] ?? 'en')], 404);
        }

        return response()->json(new UserResource($user));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $formData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'active' => 'required|boolean',
        ]);

        $user = User::create([
            'name' => $formData['name'],
            'email' => $formData['email'],
            'password' => bcrypt($formData['password']),
            'active' => $formData['active'],
        ]);

        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $formData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'active' => 'sometimes|required|boolean',
            'role' => 'required|string|exists:roles,name',
            'role' => 'sometimes|required|string',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => __('User not found', $_SESSION['locale'] ?? 'en')], 404);
        }

        $user->update($formData);
        if (isset($formData['role'])) {
            $user->roles()->sync($formData['role']);
        }

        return response()->json(new UserResource($user));
    }

    public function updatePassword(Request $request, string $id)
    {
        $formData = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => __('User not found', $_SESSION['locale'] ?? 'en')], 404);
        }

        $user->update([
            'password' => bcrypt($formData['password']),
        ]);

        return response()->json([
            'message' => __('Password updated successfully', $_SESSION['locale'] ?? 'en'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => __('User not found', $_SESSION['locale'] ?? 'en')], 404);
        }

        $user->delete();

        return response()->json([
            'message' => __('User deleted successfully', $_SESSION['locale'] ?? 'en'),
        ]);
    }
}
