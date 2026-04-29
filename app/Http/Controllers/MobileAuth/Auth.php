<?php

namespace App\Http\Controllers\MobileAuth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class Auth extends Controller
{
    /**
     * Full credential login (email + password).
     * Called on first login or when the device session has expired.
     * Returns user info including whether a PIN has already been set.
     */
    public function login(Request $request)
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            $pid = Profile::where('email', $email)->value('profile_id');

            if (empty($pid)) {
                throw new Exception('Invalid email!');
            }

            $user = User::where('profile_id', $pid)->first();

            if (! Hash::check($password, $user->password)) {
                throw new Exception('Invalid password!');
            }

            // Revoke previous mobile tokens to keep only one active session per device
            $user->tokens()->where('name', 'mobile-token')->delete();
            $token = $user->createToken('mobile-token')->plainTextToken;

            $info = [
                'user' => $user,
                'profile' => $user->profile,
                'role_name' => $user->profile?->role?->name,
                'token' => $token,
                'has_pin' => ! is_null($user->pin), // tells the app whether to show PIN setup
            ];

            return response()->json([
                'message' => 'Login Successfully!',
                'data' => $info,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to login!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set or update the user's 4-digit PIN.
     * Must be called after a successful full credential login.
     * Route: POST /api/mobile-set-pin   (auth:sanctum middleware)
     */
    public function setPin(Request $request)
    {
        try {
            $pin = $request->input('pin');

            if (! preg_match('/^\d{4}$/', (string) $pin)) {
                throw new Exception('PIN must be exactly 4 digits.');
            }

            $user = $request->user();
            $user->pin = Hash::make($pin);
            $user->save();

            return response()->json(['message' => 'PIN set successfully.'], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to set PIN.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify PIN login for a registered device (within the 1-month window).
     * The client must send the Sanctum token it stored locally so we know which user this is.
     * Route: POST /api/mobile-verify-pin   (auth:sanctum middleware)
     */
    public function verifyPin(Request $request)
    {
        try {
            $pin = $request->input('pin');
            $user = $request->user(); // resolved from the stored Bearer token

            if (is_null($user->pin)) {
                throw new Exception('No PIN has been set for this account.');
            }

            if (! Hash::check($pin, $user->pin)) {
                throw new Exception('Invalid PIN.');
            }

            $info = [
                'user' => $user,
                'profile' => $user->profile,
                'role_name' => $user->profile?->role?->name,
                'has_pin' => true,
            ];

            return response()->json([
                'message' => 'PIN verified successfully.',
                'data' => $info,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to verify PIN.',
                'error' => $e->getMessage(),
            ], 500);
        }
    } 

    public function saveToken(Request $request){
        $request->validate([
            'user_id' => 'required',
            'token' => 'required'
        ]);

        User::where('user_id', $request->user_id)
            ->update([
                'fcm_token' => $request->token
            ]);

        return response()->json([
            'message' => 'Token saved successfully'
        ]);
    }
}
