<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle web login with email and password.
     */
    public function login(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            $pid = Profile::where('email', $email)->value('profile_id');

            if (empty($pid)) {
                return response()->json([
                    'message' => 'Invalid credentials!',
                ], 401);
            }

            $user = User::where('profile_id', $pid)->first();

            if (! $user || ! Hash::check($password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials!',
                ], 401);
            }

            // Login the user using Laravel's session-based authentication
            auth()->login($user);

            return response()->json([
                'message' => 'Login successful!',
                'user' => $user,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Login failed!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully!',
        ], 200);
    }

    public function completeRegistration(Request $request)
{
    $request->validate([
        'email'                 => ['required', 'email', 'exists:profiles,email'],
        'password'              => [
            'required',
            'confirmed',
            'min:8',
            'regex:/[A-Z]/',      // at least 1 uppercase
            'regex:/[^A-Za-z0-9]/', // at least 1 special char
        ],
        'password_confirmation' => ['required'],
    ], [
        'password.regex' => 'Password must contain at least 1 uppercase letter and 1 special character.',
    ]);

    $user = \App\Models\User::whereHas('profile', fn ($q) =>
        $q->where('email', $request->email)
    )->firstOrFail();

    $user->update([
        'password'             => \Illuminate\Support\Facades\Hash::make($request->password),
        'must_change_password' => false,
    ]);

    auth()->login($user);

    return response()->json(['message' => 'Registration complete.']);
}
}
