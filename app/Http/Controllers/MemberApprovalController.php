<?php

namespace App\Http\Controllers;

use App\Models\MembershipApplication;
use Illuminate\Http\Request;

class MemberApprovalController extends Controller
{
    public function approve(Request $request, $id)
    {
        $application = MembershipApplication::findOrFail($id);

        $application->update([
            'status' => 'approved',
            'approved_at' => now(),
            'updated_by' => auth()->user()?->user_id,
        ]);

        return response()->json(['message' => 'Member approved.']);
    }
}
