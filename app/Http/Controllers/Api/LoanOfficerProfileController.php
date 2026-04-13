<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanOfficerProfileController extends Controller
{
    public function update(Request $request, $profileId)
    {
        if (! $profileId) {
            return response()->json([
                'error' => 'Profile ID is missing. Cannot update profile.',
            ], 400);
        }

        $profile = Profile::find($profileId);

        if (! $profile) {
            return response()->json([
                'error' => 'Profile not found',
                'profile_id' => $profileId,
            ], 404);
        }

        // Assuming roles_id 6 = Loan Officer
        if ($profile->roles_id !== 6) {
            return response()->json([
                'error' => 'Unauthorized',
                'role_found' => $profile->role?->name,
            ], 403);
        }

        // Validate fields for both Profile and User
        $validated = $request->validate([
            // Profile fields
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'birthdate' => 'nullable|date',
            'sex' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'roles_id' => 'nullable|integer',
            // User fields
            'username' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'coop_id' => 'nullable|string|max:255',
        ]);

        // Update Profile fields
        $profile->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? $profile->middle_name,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'mobile_number' => $validated['mobile_number'] ?? $profile->mobile_number,
            'birthdate' => $validated['birthdate'] ?? $profile->birthdate,
            'sex' => $validated['sex'] ?? $profile->sex,
            'address' => $validated['address'] ?? $profile->address,
            'roles_id' => $validated['roles_id'] ?? $profile->roles_id,
        ]);

        // Update User fields if present
        $user = $profile->user;
        if ($user) {
            $user->update([
                'username' => $validated['username'] ?? $user->username,
                'avatar' => $validated['avatar'] ?? $user->avatar,
                'coop_id' => $validated['coop_id'] ?? $user->coop_id,
            ]);

            $actorName = Auth::user()?->profile?->full_name ?? Auth::user()?->username ?? 'Staff member';
            app(NotificationService::class)->notifyUser(
                $user->user_id,
                'Profile updated by staff',
                "Your profile was updated by {$actorName}. If you did not request this change, please contact support."
            );
        }

        return response()->json([
            'message' => 'Profile and user updated successfully',
            'profile' => $profile->fresh(),
            'user' => $user ? $user->fresh() : null,
            'role' => $profile->role?->name,
        ], 200);
    }

    public function show($profileId)
    {
        $profile = Profile::find($profileId);

        if (! $profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        return response()->json([
            'profile' => $profile,
            'user' => $profile->user,
        ]);
    }
}
