<?php

namespace App\Http\Controllers\MobileAuth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use Exception;


class Account_officer_controller extends Controller
{
    // Function for mobile to get account officers
    public function mobileAccountOfficers()
    {
        // Kuhaon tanan account officers
        $officers = Profile::all(); // or with relations: ->with('user')->get();

        // Return as JSON
        return response()->json($officers);
    }

    // Function for mobile to get user profile
    public function mobileUserProfile($userId)
    {
        $user = User::with('profile')->find($userId);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }
}
