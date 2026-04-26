<?php

namespace App\Http\Controllers;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\MemberDetail;
use App\Services\LoanScheduleService;
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

    public function getDelinquentMembersList(Request $request)
    {
        try {
            $perPage = (int) $request->query('per_page', 15);
            $perPage = max(1, min($perPage, 100));
            $search = trim((string) $request->query('search', ''));

            $loanScheduleService = app(LoanScheduleService::class);

            $overdueByProfile = LoanAccount::query()
                ->where('status', 'Active')
                ->with('loanApplication.member')
                ->get()
                ->reduce(function (array $carry, LoanAccount $loanAccount) use ($loanScheduleService): array {
                    $schedule = $loanScheduleService->build($loanAccount);
                    $maxDaysOverdue = (int) (collect($schedule)
                        ->filter(fn (array $row): bool => (int) ($row['days_late'] ?? 0) > 0)
                        ->max('days_late') ?? 0);

                    if ($maxDaysOverdue <= 0) {
                        return $carry;
                    }

                    $profileId = (int) ($loanAccount->profile_id
                        ?? $loanAccount->loanApplication?->member?->profile_id
                        ?? 0);

                    if ($profileId <= 0) {
                        return $carry;
                    }

                    $carry[$profileId] = max($carry[$profileId] ?? 0, $maxDaysOverdue);

                    return $carry;
                }, []);

            $statusDelinquentProfileIds = MemberDetail::query()
                ->where('status', 'Delinquent')
                ->pluck('profile_id')
                ->filter()
                ->map(fn ($profileId): int => (int) $profileId)
                ->values()
                ->all();

            $delinquentProfileIds = collect(array_keys($overdueByProfile))
                ->merge($statusDelinquentProfileIds)
                ->unique()
                ->values()
                ->all();

            $query = MemberDetail::query()
                ->with(['profile', 'branch', 'membershipType'])
                ->whereIn('profile_id', $delinquentProfileIds);

            if ($search !== '') {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('member_no', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($profileQuery) use ($search) {
                            $profileQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('mobile_number', 'like', "%{$search}%");
                        });
                });
            }

            $delinquentMembers = $query
                ->orderByDesc('updated_at')
                ->paginate($perPage);

            $delinquentMembers->getCollection()->transform(function (MemberDetail $member) use ($overdueByProfile): MemberDetail {
                $member->setAttribute('member_detail_id', $member->getKey());
                $member->setAttribute('days_overdue', (int) ($overdueByProfile[(int) $member->profile_id] ?? 0));
                $member->setAttribute('status', 'Delinquent');

                return $member;
            });

            return response()->json($delinquentMembers);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Unable to get delinquent members list',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
