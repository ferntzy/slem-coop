<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use Exception;
use Illuminate\Http\Request;

class MobileMemberGeneral extends Controller
{
    public function getDashboardData(Request $request)
    {
        try {
            $pid = $request->pid;
            $totalLoanBalance = LoanAccount::where('profile_id', $pid)->sum('balance');

            if (! $totalLoanBalance) {
                throw new Exception('Unable to get total loan balance');
            }

            $activeLoans = LoanAccount::where('profile_id', $pid)->count();

            return response()->json([
                'activeLoans' => $activeLoans,
                'totalLoanBalance' => $totalLoanBalance,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Unable to get data!',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getActiveLoansData(Request $request)
    {
        try {
            $pid = $request->pid;

            $activeLoans = LoanAccount::where('profile_id', $pid)
                ->where('status', 'Active')
                ->latest()
                ->get();

            $loanIds = $activeLoans->pluck('loan_account_id');

            // Return payment_date strings grouped by loan_account_id
            $paidDates = CollectionAndPosting::whereIn('loan_account_id', $loanIds)
                ->whereIn('status', ['Posted', 'Draft'])
                ->get(['loan_account_id', 'payment_date'])
                ->groupBy('loan_account_id')
                ->map(fn ($rows) => $rows->pluck('payment_date')->toArray());

            return response()->json([
                'activeLoans' => $activeLoans,
                'paidDates' => $paidDates, // { loan_account_id: ["2024-01-15", "2024-02-10", ...] }
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get Data',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getLoanHistoryData(Request $request)
    {
        try {
            $pid = $request->pid;

            $loans = LoanAccount::where('profile_id', $pid)
                ->latest()
                ->get();

            if ($loans->isEmpty()) {
                throw new Exception('No active loans');
            }

            $loanIds = $loans->pluck('loan_account_id');

            $paidDates = CollectionAndPosting::whereIn('loan_account_id', $loanIds)
                ->whereIn('status', ['Posted', 'Draft'])
                ->get(['loan_account_id', 'payment_date'])
                ->groupBy('loan_account_id')
                ->map(fn ($rows) => $rows->pluck('payment_date')->toArray());

            return response()->json([
                'loans' => $loans,
                'paidDates' => $paidDates,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get Data',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getNumberOfActiveLoans()
    {
        try {
            $noal = LoanAccount::where('status', 'Active')
                ->count();

            return response()->json([
                'noal' => $noal,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get active loans',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
