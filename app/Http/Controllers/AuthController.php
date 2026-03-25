<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Get current authenticated user
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // Login (optional if you keep Blade login)
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => __('These credentials do not match our records.'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->intended('/login');
    }
}
