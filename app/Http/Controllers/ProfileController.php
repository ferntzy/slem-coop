<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:45',
            'last_name' => 'required|string|max:45',
            'email' => 'required|email|unique:profiles,email',
            'mobile_number' => 'required|string|max:45',
            'birthdate' => 'required|date',
            'sex' => 'nullable|in:Male,Female',
            'address' => 'nullable|string|max:255',
        ]);

        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $profile = Profile::create([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'mobile_number' => $validated['mobile_number'],
            'birthdate' => $validated['birthdate'],
            'sex' => $validated['sex'] ?? null,
            'address' => $validated['address'] ?? null,
            'roles_id' => $memberRole->id,
        ]);

        return response()->json([
            'profile_id' => $profile->profile_id,
        ], 201);
    }

    public function editProfile(Request $request)
    {
        try {
            $pid = $request->profile_id;

            $profile = Profile::where('profile_id', $pid)->first();
            if (! $profile) {
                return response()->json([
                    'message' => 'Profile not found.',
                ], 404);
            }

            $date = Carbon::createFromFormat('F d, Y', $request->birthdate)->format('Y-m-d');

            $profile->update([
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'address' => $request->address,
                'mobile_number' => $request->mobile_number,
                'birthdate' => $date,
            ]);

            $avatarUpdated = false;
            if ($request->hasFile('image')) {
                $user = User::where('profile_id', $pid)->first();

                $file = $request->file('image');
                $filename = 'avatar_'.$pid.'_'.time().'.'.$file->getClientOriginalExtension();

                if ($user && $user->image_path && file_exists(public_path($user->image_path))) {
                    unlink(public_path($user->image_path));
                }

                $file->move(public_path('images'), $filename);

                if ($user) {
                    $user->update(['image_path' => 'images/'.$filename]);
                }

                $avatarUpdated = true;
            }

            $user = User::where('profile_id', $pid)->first();

            $updateType = $avatarUpdated ? 'profile photo and information' : 'profile information';
            app(NotificationService::class)->notifyAdmins(
                'Member profile updated',
                "{$profile->full_name} updated their {$updateType}."
            );

            if ($user) {
                app(NotificationService::class)->notifyUser(
                    $user->user_id,
                    'Profile update confirmed',
                    "Your {$updateType} was successfully updated."
                );
            }

            $info = [
                'user' => $user,
                'profile' => $user->profile,
            ];

            return response()->json([
                'success' => 'Profile data was updated successfully!',
                'data' => $info,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to save changes to profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
