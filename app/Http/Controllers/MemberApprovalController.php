<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class MemberApprovalController extends Controller
{
    public function approve(Request $request, $id)
    {
        $application = MembershipApplication::with('profile')->findOrFail($id);

        // Update status to approved
        $application->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'updated_by'  => auth()->user()?->user_id,
        ]);

        $profile = $application->profile;

        if (!$profile) {
            return response()->json(['message' => 'Application approved but no profile found.'], 200);
        }

        $notificationService = app(NotificationService::class);

        // If no user account yet → create one + send email automatically
        if (!User::where('profile_id', $profile->profile_id)->exists()) {
            $notificationService->createUserWithAutoPassword($profile);
        }

        return response()->json(['message' => 'Member approved and email notification sent.']);
    }
}