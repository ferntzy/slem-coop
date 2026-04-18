<?php

namespace App\Http\Controllers\Api;

use App\Models\LoanApplication as ModelsLoanApplication;
use App\Models\MemberDetail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountLoansController extends Controller
{
    /**
     * GET loans for a specific member
     * Example: /api/loans?profile_id=5
     */
    public function Loans(Request $request)
    {
        try {
            // Get profile_id from query parameter
            $profileId = $request->query('profile_id');

            if (!$profileId) {
                return response()->json([
                    'message' => 'Unable to fetch loans',
                    'error' => 'The profile_id query parameter is required.'
                ], 400);
            }

            // Get Member
            $member = MemberDetail::where('profile_id', $profileId)->first();

            if (!$member) {
                return response()->json([
                    'message' => 'Unable to fetch loans',
                    'error' => 'Member not found.'
                ], 404);
            }

            // Fetch loans for that member
            $loans = ModelsLoanApplication::where('member_id', $member->id)
                ->with(['type', 'member']) // eager load relationships
                ->get()
                ->map(function ($loan) use ($member) {
                    return [
                        'loan_application_id' => $loan->loan_application_id,
                        'member_name' => $member->full_name,
                        'loan_type' => $loan->type->name ?? null,
                        'amount_requested' => number_format($loan->amount_requested, 2),
                        'loan_status' => $loan->status,
                        'term_months' => $loan->term_months,
                        'release_date' => $loan->release_date ?? null
                    ];
                });

            return response()->json($loans, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch loans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET all loans (no member filter)
     * Example: /api/all-loans
     */
    public function allLoans()
    {
        try {
            $loans = ModelsLoanApplication::with(['type', 'member'])
                ->get()
                ->map(function ($loan) {
                    return [
                        'loan_application_id' => $loan->loan_application_id,
                        'member_name' => $loan->member->full_name ?? null,
                        'loan_type' => $loan->type->name ?? null,
                        'amount_requested' => number_format($loan->amount_requested, 2),
                        'loan_status' => $loan->status,
                        'term_months' => $loan->term_months,
                        'release_date' => $loan->release_date ?? null
                    ];
                });

            return response()->json($loans, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch loans',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show($id)
{
    $loan = ModelsLoanApplication::with(['member', 'type'])
        ->where('loan_application_id', $id)
        ->firstOrFail();

    return response()->json([
        'loan_application_id' => $loan->loan_application_id,
        'member_name' => $loan->member->full_name,
        'loan_type' => $loan->type->name ?? null,
        'amount_requested' => $loan->amount_requested,
        'loan_status' => $loan->status,
        'term_months' => $loan->term_months,
        'release_date' => $loan->release_date,
        'purpose' => $loan->purpose,
    ]);
}
}
