<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MemberDetail;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Profile;

class AccountMembersController extends Controller
{
    public function member()
    {
        // Eager load 'branch' and 'membershipType' relationships
        $activeMembers = MemberDetail::with(['branch', 'membershipType'])
            ->where('status', 'active')
            ->get()
            ->map(function($member) {
                return [
                    'id' => $member->id,
                    'full_name' => $member->fullName, // uses accessor
                    'email' => $member->email ?? 'N/A',
                    'contact_no' => $member->contact_no ?? 'N/A',
                    'member_no' => $member->member_no,
                    'occupation' => $member->occupation,
                    'employer_name' => $member->employer_name,
                    'share_capital_balance' => $member->share_capital_balance,
                    'status' => $member->status,
                    'branch_id' => $member->branch_id,
                    'branch_name' => $member->branch?->name ?? 'N/A', // added branch name
                    'membership_type_id' => $member->membership_type_id,
                    'membership_type' => $member->membershipType?->name ?? 'N/A', // added membership type name
                ];
            });

        return response()->json([
            'active_members' => $activeMembers,
        ]);
    }
public function show($id)
{
    $member = MemberDetail::with(['branch', 'membershipType', 'profile'])
        ->where('id', $id)
        ->first();

    if (!$member) {
        return response()->json(['message' => 'Member not found'], 404);
    }

    return response()->json([
        'id' => $member->id,
        'full_name' => $member->fullName,
        'member_no' => $member->member_no,
        'email' => $member->profile?->email ?? 'N/A',
        'contact_no' => $member->profile?->contact_no ?? 'N/A',
        'occupation' => $member->occupation,
        'employer_name' => $member->employer_name,
        'share_capital_balance' => $member->share_capital_balance,
        'status' => $member->status,
        'branch_id' => $member->branch_id,
        'branch_name' => $member->branch?->name ?? 'N/A',
        'membership_type_id' => $member->membership_type_id,
        'membership_type' => $member->membershipType?->name ?? 'N/A',
    ]);
}

}
