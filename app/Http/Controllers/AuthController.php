<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::guard('client')->attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json([
                'success' => true,
                'redirect' => route('home'),
            ]);
        }
        return response()->json(['message' => 'Invalid credentials.'], 401);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:clients,email',
            'code' => 'nullable|string|max:10',
            'phone' => 'required|string|max:20',
            'password' => 'required',
            // 'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $client = Client::create([
                'name' => $request->name,
                'phone_code' => $request->code,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'original_password' => $request->password,
                'is_active' => true,
                'accept_notification' => true,
            ]);

            return response()->json([
                'message' => 'Registration successful',
                'redirect' => route('home')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during registration',
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }
}
