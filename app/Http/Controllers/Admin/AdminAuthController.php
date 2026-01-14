<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // Show the admin login form
    public function loginPage()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
            // if (Auth::user()->hasRole('admin')) {
            // }
            // if (Auth::user()->hasRole('agent')) {
            //     return redirect()->route('agent.dashboard');
            // }
        }

        return view('admin.login');
    }

    // Handle admin login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $request->session()->regenerate();
            return response()->json(['success' => true, 'redirect' => route('admin.dashboard')]);
            // if ($user->hasRole('admin')) {
            // }

            // if ($user->hasRole('agent')) {
            //     $request->session()->regenerate();
            //     return response()->json(['success' => true, 'redirect' => route('agent.dashboard')]);
            // }

            Auth::logout();
            return response()->json(['message' => 'Access denied.'], 403);
        }

        // Failed login
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }


    // Handle admin logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }
}