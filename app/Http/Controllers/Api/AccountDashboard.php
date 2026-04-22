<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\MemberDetail;
use Carbon\Carbon;

class AccountDashboard extends Controller
{
    public function activemembers()
    {
        $prevEnd = Carbon::now()->subMonth()->endOfMonth();

        $activeMembers = MemberDetail::where('status', 'Active')->count();
        $prevActiveMembers = MemberDetail::where('status', 'Active')
            ->where('created_at', '<', $prevEnd)
            ->count();

        $memberChange = $prevActiveMembers > 0
            ? round((($activeMembers - $prevActiveMembers) / $prevActiveMembers) * 100, 1)
            : 0;

        return response()->json([
            'active_members' => $activeMembers,
            'member_change_percentage' => $memberChange,
        ]);
    }

      public function loanDisbursements()
    {
        // Define current period (e.g., this month)
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Define previous period (previous month)
        $prevStart = Carbon::now()->subMonth()->startOfMonth();
        $prevEnd = Carbon::now()->subMonth()->endOfMonth();

        // Loan Disbursements this period
        $disbursed = LoanApplication::where('status', 'Approved')
            ->whereBetween('approved_at', [$start, $end])
            ->sum('amount_requested');

        $prevDisbursed = LoanApplication::where('status', 'Approved')
            ->whereBetween('approved_at', [$prevStart, $prevEnd])
            ->sum('amount_requested');

        $disbursedChange = $prevDisbursed > 0
            ? round((($disbursed - $prevDisbursed) / $prevDisbursed) * 100, 1)
            : 0;

        return response()->json([
            'disbursed_this_period' => $disbursed,
            'disbursed_previous_period' => $prevDisbursed,
            'disbursed_change_percentage' => $disbursedChange,
        ]);
    }

    public function collections()
    {
        // Define current period (e.g., this month)
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Define previous period (previous month)
        $prevStart = Carbon::now()->subMonth()->startOfMonth();
        $prevEnd = Carbon::now()->subMonth()->endOfMonth();

        // Collections this period
        $collected = CollectionAndPosting::where('status', 'Posted')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount_paid');

        $prevCollected = CollectionAndPosting::where('status', 'Posted')
            ->whereBetween('payment_date', [$prevStart, $prevEnd])
            ->sum('amount_paid');

        $collectedChange = $prevCollected > 0
            ? round((($collected - $prevCollected) / $prevCollected) * 100, 1)
            : 0;

        return response()->json([
            'collected_this_period' => $collected,
            'collected_previous_period' => $prevCollected,
            'collected_change_percentage' => $collectedChange,
        ]);
    }

    // Active Loan Accounts
    public function activeLoanAccounts()
    {
        // Define current period (e.g., this month)
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Define previous period (previous month)
        $prevStart = Carbon::now()->subMonth()->startOfMonth();
        $prevEnd = Carbon::now()->subMonth()->endOfMonth();

        // Active Loan Accounts
        $activeLoans = LoanAccount::where('status', 'Active')->count();

        $prevActiveLoans = LoanAccount::where('status', 'Active')
            ->where('created_at', '<', $prevEnd)
            ->count();

        $loansChange = $prevActiveLoans > 0
            ? round((($activeLoans - $prevActiveLoans) / $prevActiveLoans) * 100, 1)
            : 0;

        return response()->json([
            'active_loans' => $activeLoans,
            'active_loans_previous_period' => $prevActiveLoans,
            'active_loans_change_percentage' => $loansChange,
        ]);
    }

    // Pending Loan Applications
    public function pendingLoanApplications()
    {
        // Define current period (e.g., this month)
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        // Define previous period (previous month)
        $prevStart = Carbon::now()->subMonth()->startOfMonth();
        $prevEnd = Carbon::now()->subMonth()->endOfMonth();

        // Pending Loan Applications this period
        $pendingLoans = LoanApplication::where('status', 'Pending')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $prevPendingLoans = LoanApplication::where('status', 'Pending')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $pendingChange = $prevPendingLoans > 0
            ? round((($pendingLoans - $prevPendingLoans) / $prevPendingLoans) * 100, 1)
            : 0;

        return response()->json([
            'pending_loans' => $pendingLoans,
            'pending_loans_previous_period' => $prevPendingLoans,
            'pending_loans_change_percentage' => $pendingChange,
        ]);
    }

    // Delinquent Members
    public function delinquentMembers()
    {

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $prevStart = Carbon::now()->subMonth()->startOfMonth();
        $prevEnd = Carbon::now()->subMonth()->endOfMonth();

        $delinquentMembers = MemberDetail::where('status', 'Delinquent')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $prevDelinquentMembers = MemberDetail::where('status', 'Delinquent')
            ->whereBetween('updated_at', [$prevStart, $prevEnd])
            ->count();

        $delinquentChange = $prevDelinquentMembers > 0
            ? round((($delinquentMembers - $prevDelinquentMembers) / $prevDelinquentMembers) * 100, 1)
            : 0;

        return response()->json([
            'delinquent_members' => $delinquentMembers,
            'delinquent_members_previous_period' => $prevDelinquentMembers,
            'delinquent_members_change_percentage' => $delinquentChange,
        ]);
    }
}
