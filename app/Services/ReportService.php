<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CollectionAndPosting;
use App\Models\CoopSetting;
use App\Models\DailyCollectionEntry;
use App\Models\LoanAccount;
use App\Models\LoanPayment;
use App\Models\MemberDetail;
use App\Models\SavingsAccountTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(
        protected LoanScheduleService $loanScheduleService,
    ) {}

    public function build(string $reportKey, array $filters, User $user): array
    {
        $normalizedFilters = $this->normalizeFilters($filters, $user);

        return match ($reportKey) {
            'daily-collection' => $this->buildDailyCollectionReport($normalizedFilters, $user),
            'loan-tracking' => $this->buildLoanTrackingReport($normalizedFilters, $user),
            'system-summary' => $this->buildSystemSummaryReport($normalizedFilters, $user),
            'delinquency' => $this->buildDelinquencyReport($normalizedFilters, $user),
            'member-statement' => $this->buildMemberStatementReport($normalizedFilters, $user),
            default => throw new \InvalidArgumentException("Unknown report key [{$reportKey}]."),
        };
    }

    protected function normalizeFilters(array $filters, User $user): array
    {
        $start = Carbon::parse($filters['startDate'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $end = Carbon::parse($filters['endDate'] ?? now()->toDateString())->endOfDay();

        if ($end->lessThan($start)) {
            $end = $start->copy()->endOfDay();
        }

        $branchId = isset($filters['branchId']) && $filters['branchId'] !== ''
            ? (int) $filters['branchId']
            : null;

        $memberId = isset($filters['memberId']) && $filters['memberId'] !== ''
            ? (int) $filters['memberId']
            : null;

        if ($user->isBranchScoped() && $user->branchId()) {
            $branchId = $user->branchId();
        }

        return [
            'start' => $start,
            'end' => $end,
            'branch_id' => $branchId,
            'member_id' => $memberId,
            'branch_name' => $this->resolveBranchName($branchId),
            'member_name' => $this->resolveMemberName($memberId),
        ];
    }

    protected function buildDailyCollectionReport(array $filters, User $user): array
    {
        $postedPayments = $this->dailyLoanPaymentQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanAccount.profile.memberDetail.branch',
                'postedBy.profile',
            ])
            ->orderBy('payment_date')
            ->orderBy('loan_payment_id')
            ->get();

        $postedCollections = $this->dailyCollectionPostingQuery($filters)
            ->with([
                'loanAccount.profile.memberDetail.branch',
                'postedBy.profile',
            ])
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get();

        $dailyEntries = $this->dailyCollectionEntryQuery($filters)
            ->with([
                'ao.profile',
                'ao.staffDetail.branch',
                'verifiedBy.profile',
            ])
            ->orderBy('collection_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $paymentRows = $postedPayments->map(function (LoanPayment $payment): array {
            $memberName = $payment->loanApplication?->member?->profile?->full_name
                ?? $payment->loanAccount?->profile?->full_name
                ?? 'Unknown Member';

            $loanLabel = $payment->loanAccount?->loan_account_id
                ? 'Loan #'.$payment->loanAccount->loan_account_id
                : 'Loan App #'.$payment->loan_application_id;

            return [
                'payment_date' => $payment->payment_date?->format('M d, Y') ?? '—',
                'member' => $memberName,
                'loan_reference' => $loanLabel,
                'amount_paid' => $this->money($payment->amount_paid),
                'principal_paid' => $this->money($payment->principal_paid),
                'interest_paid' => $this->money($payment->interest_paid),
                'penalty_paid' => $this->money($payment->penalty_paid),
                'payment_type' => $payment->payment_type ?? '—',
                'status' => $payment->status ?? '—',
                'posted_by' => $payment->postedBy?->name ?? 'System',
            ];
        })->values();

        $methodRows = $postedCollections
            ->groupBy('payment_method')
            ->map(function (Collection $collections, string $method): array {
                return [
                    'payment_method' => $method,
                    'transaction_count' => number_format($collections->count()),
                    'amount_paid' => $this->money($collections->sum('amount_paid')),
                ];
            })
            ->values();

        $entryRows = $dailyEntries->map(function (DailyCollectionEntry $entry): array {
            return [
                'collection_date' => $entry->collection_date?->format('M d, Y') ?? '—',
                'account_officer' => $entry->ao?->profile?->full_name ?? $entry->ao?->name ?? 'Unknown AO',
                'system_total' => $this->money($entry->system_total),
                'cash_on_hand' => $this->money($entry->cash_on_hand),
                'variance' => $this->money($entry->variance),
                'status' => $entry->status,
                'submitted_at' => $entry->submitted_at?->format('M d, Y h:i A') ?? '—',
                'verified_by' => $entry->verifiedBy?->name ?? '—',
            ];
        })->values();

        $totalPayments = (float) $postedPayments->sum('amount_paid');
        $totalPrincipal = (float) $postedPayments->sum('principal_paid');
        $totalInterest = (float) $postedPayments->sum('interest_paid');
        $totalPenalty = (float) $postedPayments->sum('penalty_paid');
        $cashOnHand = (float) $dailyEntries->sum('cash_on_hand');
        $variance = (float) $dailyEntries->sum('variance');

        return $this->baseReportPayload(
            title: 'Daily Collection Report',
            subtitle: 'Payments posted within the selected period, including cashier method breakdown and AO collection entries.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Total Payments', 'value' => $this->money($totalPayments)],
                ['label' => 'Principal', 'value' => $this->money($totalPrincipal)],
                ['label' => 'Interest', 'value' => $this->money($totalInterest)],
                ['label' => 'Penalty', 'value' => $this->money($totalPenalty)],
                ['label' => 'Transactions', 'value' => number_format($postedPayments->count())],
                ['label' => 'Cash on Hand', 'value' => $this->money($cashOnHand)],
                ['label' => 'Variance', 'value' => $this->money($variance)],
            ],
            mainTable: [
                'title' => 'Posted Loan Payments',
                'columns' => [
                    ['key' => 'payment_date', 'label' => 'Date'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'amount_paid', 'label' => 'Amount Paid', 'align' => 'right'],
                    ['key' => 'principal_paid', 'label' => 'Principal', 'align' => 'right'],
                    ['key' => 'interest_paid', 'label' => 'Interest', 'align' => 'right'],
                    ['key' => 'penalty_paid', 'label' => 'Penalty', 'align' => 'right'],
                    ['key' => 'payment_type', 'label' => 'Type'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'posted_by', 'label' => 'Posted By'],
                ],
                'rows' => $paymentRows,
                'totals_label' => 'Totals',
                'totals' => [
                    'amount_paid' => $this->money($totalPayments),
                    'principal_paid' => $this->money($totalPrincipal),
                    'interest_paid' => $this->money($totalInterest),
                    'penalty_paid' => $this->money($totalPenalty),
                ],
            ],
            sections: [
                [
                    'title' => 'Payment Method Breakdown',
                    'columns' => [
                        ['key' => 'payment_method', 'label' => 'Method'],
                        ['key' => 'transaction_count', 'label' => 'Transactions', 'align' => 'right'],
                        ['key' => 'amount_paid', 'label' => 'Amount', 'align' => 'right'],
                    ],
                    'rows' => $methodRows,
                    'totals_label' => 'Total',
                    'totals' => [
                        'transaction_count' => number_format($postedCollections->count()),
                        'amount_paid' => $this->money($postedCollections->sum('amount_paid')),
                    ],
                ],
                [
                    'title' => 'Daily Collection Entries',
                    'columns' => [
                        ['key' => 'collection_date', 'label' => 'Date'],
                        ['key' => 'account_officer', 'label' => 'Account Officer'],
                        ['key' => 'system_total', 'label' => 'System Total', 'align' => 'right'],
                        ['key' => 'cash_on_hand', 'label' => 'Cash on Hand', 'align' => 'right'],
                        ['key' => 'variance', 'label' => 'Variance', 'align' => 'right'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'submitted_at', 'label' => 'Submitted At'],
                        ['key' => 'verified_by', 'label' => 'Verified By'],
                    ],
                    'rows' => $entryRows,
                    'totals_label' => 'Totals',
                    'totals' => [
                        'system_total' => $this->money($dailyEntries->sum('system_total')),
                        'cash_on_hand' => $this->money($cashOnHand),
                        'variance' => $this->money($variance),
                    ],
                ],
            ],
        );
    }

    protected function buildLoanTrackingReport(array $filters, User $user): array
    {
        $loanAccounts = $this->loanAccountQuery($filters)
            ->with([
                'loanApplication.type',
                'loanApplication.member.profile',
                'profile.memberDetail.branch',
            ])
            ->orderByDesc('release_date')
            ->orderByDesc('loan_account_id')
            ->get();

        $rows = $loanAccounts->map(function (LoanAccount $loanAccount): array {
            $schedule = $this->loanScheduleService->build($loanAccount);
            $summary = $this->summarizeLoanSchedule($schedule);

            return [
                'member' => $loanAccount->loanApplication?->member?->profile?->full_name
                    ?? $loanAccount->profile?->full_name
                    ?? 'Unknown Member',
                'loan_type' => $loanAccount->loanApplication?->type?->name ?? '—',
                'status' => $loanAccount->status ?? '—',
                'release_date' => $loanAccount->release_date?->format('M d, Y') ?? '—',
                'remaining_balance' => $this->money((float) $loanAccount->balance),
                'remaining_principal' => $this->money($summary['remaining_principal']),
                'interest' => $this->money($summary['remaining_interest']),
                'penalties' => $this->money($summary['remaining_penalties']),
                'next_due_date' => $summary['next_due_date']?->format('M d, Y') ?? '—',
                'months_left' => $summary['months_left'] > 0 ? number_format($summary['months_left']) : '—',
            ];
        })->values();

        $outstandingBalance = (float) $loanAccounts->sum('balance');
        $remainingPrincipal = $rows->sum(fn (array $row): float => $this->parseMoney($row['remaining_principal']));
        $remainingInterest = $rows->sum(fn (array $row): float => $this->parseMoney($row['interest']));
        $remainingPenalties = $rows->sum(fn (array $row): float => $this->parseMoney($row['penalties']));

        return $this->baseReportPayload(
            title: 'Loan Tracking Report',
            subtitle: 'Track loan balances, due dates, and repayment progress across active accounts.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Loan Accounts', 'value' => number_format($loanAccounts->count())],
                ['label' => 'Outstanding Balance', 'value' => $this->money($outstandingBalance)],
                ['label' => 'Remaining Principal', 'value' => $this->money($remainingPrincipal)],
                ['label' => 'Interest Outstanding', 'value' => $this->money($remainingInterest)],
                ['label' => 'Penalties Outstanding', 'value' => $this->money($remainingPenalties)],
            ],
            mainTable: [
                'title' => 'Loan Account Tracking',
                'columns' => [
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'release_date', 'label' => 'Release Date'],
                    ['key' => 'remaining_balance', 'label' => 'Remaining Balance', 'align' => 'right'],
                    ['key' => 'remaining_principal', 'label' => 'Principal', 'align' => 'right'],
                    ['key' => 'interest', 'label' => 'Interest', 'align' => 'right'],
                    ['key' => 'penalties', 'label' => 'Penalties', 'align' => 'right'],
                    ['key' => 'next_due_date', 'label' => 'Next Due Date'],
                    ['key' => 'months_left', 'label' => 'Months Left', 'align' => 'right'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'remaining_balance' => $this->money($outstandingBalance),
                    'remaining_principal' => $this->money($remainingPrincipal),
                    'interest' => $this->money($remainingInterest),
                    'penalties' => $this->money($remainingPenalties),
                ],
            ],
        );
    }

    protected function buildSystemSummaryReport(array $filters, User $user): array
    {
        $memberQuery = MemberDetail::query()
            ->when($filters['member_id'], fn (Builder $query, int $memberId) => $query->whereKey($memberId))
            ->when($filters['branch_id'], fn (Builder $query, int $branchId) => $query->where('branch_id', $branchId));

        $loanAccountQuery = LoanAccount::query()
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            });

        $paymentQuery = LoanPayment::query()
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            });

        $savingsQuery = SavingsAccountTransaction::query()
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('member.memberDetail', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('member.memberDetail', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            });

        $members = (clone $memberQuery)
            ->where('created_at', '<=', $filters['end'])
            ->get();

        $activeMembers = (clone $memberQuery)
            ->where('status', 'Active')
            ->count();

        $inactiveMembers = (clone $memberQuery)
            ->where('status', 'Inactive')
            ->count();

        $delinquentMembers = (clone $memberQuery)
            ->where('status', 'Delinquent')
            ->count();

        $savingsDeposits = (clone $savingsQuery)
            ->where('type', 'Deposit')
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->sum('amount');

        $savingsWithdrawals = (clone $savingsQuery)
            ->where('type', 'Withdrawal')
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->sum('amount');

        $totalSavings = (clone $savingsQuery)->sum(DB::raw("CASE WHEN type = 'Deposit' THEN amount ELSE amount * -1 END"));

        $activeLoans = (clone $loanAccountQuery)
            ->where('status', 'Active')
            ->count();

        $loanPortfolioValue = (clone $loanAccountQuery)
            ->where('status', 'Active')
            ->sum('balance');

        $loanReleases = (clone $loanAccountQuery)
            ->whereBetween('release_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->sum(DB::raw('COALESCE(net_release_amount, principal_amount)'));

        $paymentsCollected = (clone $paymentQuery)
            ->whereBetween('payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->sum('amount_paid');

        $interestEarned = (clone $paymentQuery)
            ->whereBetween('payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->sum('interest_paid');

        $penaltiesCollected = (clone $paymentQuery)
            ->whereBetween('payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->sum('penalty_paid');

        $outstandingLoans = (clone $loanAccountQuery)
            ->whereIn('status', ['Active', 'Restructured'])
            ->sum('balance');

        $memberRows = [
            ['metric' => 'Active Members', 'value' => number_format($activeMembers)],
            ['metric' => 'Inactive Members', 'value' => number_format($inactiveMembers)],
            ['metric' => 'Delinquent Members', 'value' => number_format($delinquentMembers)],
            ['metric' => 'Total Members', 'value' => number_format($members->count())],
        ];

        $financialRows = [
            ['metric' => 'Total Savings', 'value' => $this->money($totalSavings)],
            ['metric' => 'Total Deposits', 'value' => $this->money($savingsDeposits)],
            ['metric' => 'Withdrawals', 'value' => $this->money($savingsWithdrawals)],
            ['metric' => 'Loan Releases', 'value' => $this->money($loanReleases)],
            ['metric' => 'Payments Collected', 'value' => $this->money($paymentsCollected)],
            ['metric' => 'Outstanding Loans', 'value' => $this->money($outstandingLoans)],
            ['metric' => 'Net Income', 'value' => $this->money($interestEarned + $penaltiesCollected)],
        ];

        return $this->baseReportPayload(
            title: 'System Summary Report',
            subtitle: 'High-level operational and financial summary for the selected period.',
            filters: $filters,
            user: $user,
            orientation: 'portrait',
            summaryCards: [
                ['label' => 'Active Members', 'value' => number_format($activeMembers)],
                ['label' => 'Active Loans', 'value' => number_format($activeLoans)],
                ['label' => 'Total Savings', 'value' => $this->money($totalSavings)],
                ['label' => 'Loan Portfolio', 'value' => $this->money($loanPortfolioValue)],
                ['label' => 'Net Income', 'value' => $this->money($interestEarned + $penaltiesCollected)],
            ],
            mainTable: [
                'title' => 'Membership Summary',
                'columns' => [
                    ['key' => 'metric', 'label' => 'Metric'],
                    ['key' => 'value', 'label' => 'Value', 'align' => 'right'],
                ],
                'rows' => $memberRows,
            ],
            sections: [
                [
                    'title' => 'Financial Summary',
                    'columns' => [
                        ['key' => 'metric', 'label' => 'Metric'],
                        ['key' => 'value', 'label' => 'Value', 'align' => 'right'],
                    ],
                    'rows' => $financialRows,
                    'totals_label' => 'Summary',
                    'totals' => [
                        'value' => $this->money($interestEarned + $penaltiesCollected),
                    ],
                ],
            ],
        );
    }

    protected function buildDelinquencyReport(array $filters, User $user): array
    {
        $loanAccounts = $this->loanAccountQuery($filters)
            ->where('status', 'Active')
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
                'profile.memberDetail.branch',
            ])
            ->orderBy('maturity_date')
            ->orderBy('loan_account_id')
            ->get();

        $rows = $loanAccounts
            ->map(function (LoanAccount $loanAccount): ?array {
                $schedule = $this->loanScheduleService->build($loanAccount);
                $summary = $this->summarizeLoanSchedule($schedule);

                if (! $summary['days_overdue']) {
                    return null;
                }

                return [
                    'member' => $loanAccount->loanApplication?->member?->profile?->full_name
                        ?? $loanAccount->profile?->full_name
                        ?? 'Unknown Member',
                    'branch' => $loanAccount->profile?->memberDetail?->branch?->name ?? '—',
                    'loan_reference' => 'Loan #'.$loanAccount->loan_account_id,
                    'days_overdue' => number_format($summary['days_overdue']),
                    'next_due_date' => $summary['next_due_date']?->format('M d, Y') ?? '—',
                    'outstanding_balance' => $this->money((float) $loanAccount->balance),
                    'principal_due' => $this->money($summary['remaining_principal']),
                    'interest_due' => $this->money($summary['remaining_interest']),
                    'penalties' => $this->money($summary['remaining_penalties']),
                    'status' => $loanAccount->status,
                ];
            })
            ->filter()
            ->values();

        $delinquentMembers = $rows->pluck('member')->unique()->count();
        $totalOutstanding = $rows->sum(fn (array $row): float => $this->parseMoney($row['outstanding_balance']));
        $totalPenalties = $rows->sum(fn (array $row): float => $this->parseMoney($row['penalties']));
        $averageDays = $rows->isNotEmpty()
            ? round($rows->avg(fn (array $row): float => (float) str_replace(',', '', $row['days_overdue'])), 1)
            : 0;

        return $this->baseReportPayload(
            title: 'Delinquency Report',
            subtitle: 'Accounts with overdue schedules and delinquent balances in the selected period.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Delinquent Members', 'value' => number_format($delinquentMembers)],
                ['label' => 'Overdue Accounts', 'value' => number_format($rows->count())],
                ['label' => 'Outstanding Balance', 'value' => $this->money($totalOutstanding)],
                ['label' => 'Penalties', 'value' => $this->money($totalPenalties)],
                ['label' => 'Average Days Overdue', 'value' => number_format($averageDays, 1)],
            ],
            mainTable: [
                'title' => 'Delinquent Loan Accounts',
                'columns' => [
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'days_overdue', 'label' => 'Days Overdue', 'align' => 'right'],
                    ['key' => 'next_due_date', 'label' => 'Next Due Date'],
                    ['key' => 'outstanding_balance', 'label' => 'Outstanding Balance', 'align' => 'right'],
                    ['key' => 'principal_due', 'label' => 'Principal Due', 'align' => 'right'],
                    ['key' => 'interest_due', 'label' => 'Interest Due', 'align' => 'right'],
                    ['key' => 'penalties', 'label' => 'Penalties', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'outstanding_balance' => $this->money($totalOutstanding),
                    'principal_due' => $this->money($rows->sum(fn (array $row): float => $this->parseMoney($row['principal_due']))),
                    'interest_due' => $this->money($rows->sum(fn (array $row): float => $this->parseMoney($row['interest_due']))),
                    'penalties' => $this->money($totalPenalties),
                ],
            ],
        );
    }

    protected function buildMemberStatementReport(array $filters, User $user): array
    {
        $memberQuery = $this->memberScopeQuery($filters)
            ->with(['profile', 'branch']);

        $members = $memberQuery->get();
        $profileIds = $members
            ->pluck('profile_id')
            ->filter()
            ->unique()
            ->values();

        $savingsTransactions = SavingsAccountTransaction::query()
            ->with(['member.memberDetail.branch', 'postedBy.profile'])
            ->when($profileIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('profile_id', $profileIds->all()))
            ->whereBetween('transaction_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $loanAccounts = LoanAccount::query()
            ->with([
                'loanApplication.type',
                'loanApplication.member.profile',
                'profile.memberDetail.branch',
            ])
            ->when($profileIds->isNotEmpty(), function (Builder $query) use ($profileIds): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereIn('profile_id', $profileIds->all()));
            })
            ->orderByDesc('release_date')
            ->orderByDesc('loan_account_id')
            ->get();

        $loanPayments = LoanPayment::query()
            ->with([
                'loanApplication.member.profile',
                'loanAccount.loanApplication.type',
                'postedBy.profile',
            ])
            ->when($profileIds->isNotEmpty(), function (Builder $query) use ($profileIds): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereIn('profile_id', $profileIds->all()));
            })
            ->whereBetween('payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->orderByDesc('payment_date')
            ->orderByDesc('loan_payment_id')
            ->get();

        $savingsRows = $savingsTransactions->map(function (SavingsAccountTransaction $transaction): array {
            return [
                'date' => $transaction->transaction_date?->format('M d, Y') ?? '—',
                'member' => $transaction->member?->full_name ?? 'Unknown Member',
                'type' => $transaction->type ?? $transaction->direction ?? '—',
                'amount' => $this->money($transaction->amount),
                'status' => $transaction->status ?? '—',
                'reference' => $transaction->reference_no ?? '—',
                'posted_by' => $transaction->postedBy?->name ?? 'System',
            ];
        })->values();

        $loanRows = $loanAccounts->map(function (LoanAccount $loanAccount): array {
            return [
                'member' => $loanAccount->loanApplication?->member?->profile?->full_name
                    ?? $loanAccount->profile?->full_name
                    ?? 'Unknown Member',
                'branch' => $loanAccount->profile?->memberDetail?->branch?->name ?? '—',
                'loan_type' => $loanAccount->loanApplication?->type?->name ?? '—',
                'release_date' => $loanAccount->release_date?->format('M d, Y') ?? '—',
                'maturity_date' => $loanAccount->maturity_date?->format('M d, Y') ?? '—',
                'status' => $loanAccount->status ?? '—',
                'balance' => $this->money($loanAccount->balance),
            ];
        })->values();

        $paymentRows = $loanPayments->map(function (LoanPayment $payment): array {
            return [
                'date' => $payment->payment_date?->format('M d, Y') ?? '—',
                'member' => $payment->loanApplication?->member?->profile?->full_name
                    ?? $payment->loanAccount?->profile?->full_name
                    ?? 'Unknown Member',
                'loan_reference' => $payment->loanAccount?->loan_account_id
                    ? 'Loan #'.$payment->loanAccount->loan_account_id
                    : 'Loan App #'.$payment->loan_application_id,
                'amount_paid' => $this->money($payment->amount_paid),
                'principal_paid' => $this->money($payment->principal_paid),
                'interest_paid' => $this->money($payment->interest_paid),
                'penalty_paid' => $this->money($payment->penalty_paid),
                'status' => $payment->status ?? '—',
                'posted_by' => $payment->postedBy?->name ?? 'System',
            ];
        })->values();

        $savingsDeposits = (float) $savingsTransactions->where('type', 'Deposit')->sum('amount');
        $savingsWithdrawals = (float) $savingsTransactions->where('type', 'Withdrawal')->sum('amount');
        $netSavings = $savingsDeposits - $savingsWithdrawals;
        $activeLoans = $loanAccounts->where('status', 'Active')->count();
        $outstandingLoans = (float) $loanAccounts->whereIn('status', ['Active', 'Restructured'])->sum('balance');
        $paymentsCollected = (float) $loanPayments->sum('amount_paid');

        return $this->baseReportPayload(
            title: 'Member Statement Report',
            subtitle: 'Savings, loan balances, and payment activity for the selected member scope.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Members in Scope', 'value' => number_format($members->count())],
                ['label' => 'Deposits', 'value' => $this->money($savingsDeposits)],
                ['label' => 'Withdrawals', 'value' => $this->money($savingsWithdrawals)],
                ['label' => 'Net Savings', 'value' => $this->money($netSavings)],
                ['label' => 'Active Loans', 'value' => number_format($activeLoans)],
                ['label' => 'Outstanding Balance', 'value' => $this->money($outstandingLoans)],
                ['label' => 'Payments Collected', 'value' => $this->money($paymentsCollected)],
            ],
            mainTable: [
                'title' => 'Savings Transactions',
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'amount', 'label' => 'Amount', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'reference', 'label' => 'Reference'],
                    ['key' => 'posted_by', 'label' => 'Posted By'],
                ],
                'rows' => $savingsRows,
                'totals_label' => 'Totals',
                'totals' => [
                    'amount' => $this->money($netSavings),
                ],
            ],
            sections: [
                [
                    'title' => 'Loan Accounts',
                    'columns' => [
                        ['key' => 'member', 'label' => 'Member'],
                        ['key' => 'branch', 'label' => 'Branch'],
                        ['key' => 'loan_type', 'label' => 'Loan Type'],
                        ['key' => 'release_date', 'label' => 'Release Date'],
                        ['key' => 'maturity_date', 'label' => 'Maturity Date'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'balance', 'label' => 'Balance', 'align' => 'right'],
                    ],
                    'rows' => $loanRows,
                    'totals_label' => 'Totals',
                    'totals' => [
                        'balance' => $this->money($outstandingLoans),
                    ],
                ],
                [
                    'title' => 'Loan Payments',
                    'columns' => [
                        ['key' => 'date', 'label' => 'Date'],
                        ['key' => 'member', 'label' => 'Member'],
                        ['key' => 'loan_reference', 'label' => 'Loan'],
                        ['key' => 'amount_paid', 'label' => 'Amount Paid', 'align' => 'right'],
                        ['key' => 'principal_paid', 'label' => 'Principal', 'align' => 'right'],
                        ['key' => 'interest_paid', 'label' => 'Interest', 'align' => 'right'],
                        ['key' => 'penalty_paid', 'label' => 'Penalty', 'align' => 'right'],
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'posted_by', 'label' => 'Posted By'],
                    ],
                    'rows' => $paymentRows,
                    'totals_label' => 'Totals',
                    'totals' => [
                        'amount_paid' => $this->money($loanPayments->sum('amount_paid')),
                        'principal_paid' => $this->money($loanPayments->sum('principal_paid')),
                        'interest_paid' => $this->money($loanPayments->sum('interest_paid')),
                        'penalty_paid' => $this->money($loanPayments->sum('penalty_paid')),
                    ],
                ],
            ],
        );
    }

    protected function baseReportPayload(
        string $title,
        string $subtitle,
        array $filters,
        User $user,
        string $orientation,
        array $summaryCards = [],
        array $mainTable = [],
        array $sections = [],
    ): array {
        return [
            'title' => $title,
            'subtitle' => $subtitle,
            'orientation' => $orientation,
            'generated_at' => now(),
            'generated_by' => $user->name,
            'coop_name' => $this->coopName(),
            'coop_address' => $this->coopAddress(),
            'filters' => [
                'start' => $filters['start']->format('M d, Y'),
                'end' => $filters['end']->format('M d, Y'),
                'branch' => $filters['branch_name'] ?? null,
                'member' => $filters['member_name'] ?? null,
            ],
            'summary_cards' => $summaryCards,
            'main_table' => $mainTable,
            'sections' => $sections,
            'footer' => [
                'prepared_by' => $user->name,
                'verified_by' => '________________',
            ],
        ];
    }

    protected function loanAccountQuery(array $filters): Builder
    {
        return LoanAccount::query()
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereDate('release_date', '<=', $filters['end']->toDateString());
    }

    protected function dailyLoanPaymentQuery(array $filters): Builder
    {
        return LoanPayment::query()
            ->where('status', 'Posted')
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereBetween('payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()]);
    }

    protected function dailyCollectionPostingQuery(array $filters): Builder
    {
        return CollectionAndPosting::query()
            ->where('status', 'Posted')
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('loanAccount.loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('loanAccount.loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereBetween('payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()]);
    }

    protected function dailyCollectionEntryQuery(array $filters): Builder
    {
        return DailyCollectionEntry::query()
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('ao.staffDetail', fn (Builder $staffQuery) => $staffQuery->where('branch_id', $branchId));
            })
            ->whereBetween('collection_date', [$filters['start']->toDateString(), $filters['end']->toDateString()]);
    }

    protected function memberScopeQuery(array $filters): Builder
    {
        return MemberDetail::query()
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereKey($memberId);
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->where('branch_id', $branchId);
            });
    }

    protected function summarizeLoanSchedule(array $schedule): array
    {
        $rows = collect($schedule);

        $nextDueRow = $rows->first(fn (array $row): bool => ($row['status'] ?? 'Unpaid') !== 'Paid' && (float) ($row['unpaid_amount'] ?? 0) > 0)
            ?? $rows->firstWhere('status', 'Late')
            ?? $rows->firstWhere('status', 'Partial')
            ?? $rows->firstWhere('status', 'Partial / Late');

        $remainingPrincipal = $rows->sum(fn (array $row): float => max((float) ($row['scheduled_principal'] ?? 0) - (float) ($row['paid_principal'] ?? 0), 0));
        $remainingInterest = $rows->sum(fn (array $row): float => max((float) ($row['scheduled_interest'] ?? 0) - (float) ($row['paid_interest'] ?? 0), 0));
        $remainingPenalties = $rows->sum(fn (array $row): float => max((float) ($row['penalty'] ?? 0) - (float) ($row['paid_penalty'] ?? 0), 0));
        $monthsLeft = $rows->filter(fn (array $row): bool => ($row['status'] ?? 'Unpaid') !== 'Paid')->count();
        $daysOverdue = (int) ($rows->filter(fn (array $row): bool => (int) ($row['days_late'] ?? 0) > 0)->max('days_late') ?? 0);

        return [
            'remaining_principal' => round($remainingPrincipal, 2),
            'remaining_interest' => round($remainingInterest, 2),
            'remaining_penalties' => round($remainingPenalties, 2),
            'next_due_date' => isset($nextDueRow['due_date']) ? Carbon::parse($nextDueRow['due_date']) : null,
            'months_left' => $monthsLeft,
            'days_overdue' => $daysOverdue,
        ];
    }

    protected function resolveMemberName(?int $memberId): ?string
    {
        if (! $memberId) {
            return null;
        }

        $member = MemberDetail::query()->with('profile')->find($memberId);

        return $member?->profile?->full_name;
    }

    protected function resolveBranchName(?int $branchId): ?string
    {
        if (! $branchId) {
            return null;
        }

        return Branch::query()->whereKey($branchId)->value('name');
    }

    protected function coopName(): string
    {
        return (string) (CoopSetting::get('coop_name', config('app.name', 'Community Cooperative')) ?: config('app.name', 'Community Cooperative'));
    }

    protected function coopAddress(): string
    {
        return (string) (CoopSetting::get('coop_address', '') ?: '');
    }

    protected function money(float|int|string|null $amount): string
    {
        return '₱'.number_format((float) $amount, 2);
    }

    protected function parseMoney(string $amount): float
    {
        return (float) Str::of($amount)->replace(['₱', ',', ' '], '')->value();
    }
}
