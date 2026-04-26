<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CollectionAndPosting;
use App\Models\CoopSetting;
use App\Models\DailyCollectionEntry;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationStatusLog;
use App\Models\LoanPayment;
use App\Models\MemberDetail;
use App\Models\RestructureApplication;
use App\Models\RestructureApplicationStatusLog;
use App\Models\SavingsAccountTransaction;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
            'financial-summary' => $this->buildFinancialSummaryReport($normalizedFilters, $user),
            'audit-trail' => $this->buildAuditTrailReport($normalizedFilters, $user),
            'branch-performance' => $this->buildBranchPerformanceReport($normalizedFilters, $user),
            'loan-portfolio' => $this->buildLoanPortfolioReport($normalizedFilters, $user),
            'cash-flow' => $this->buildCashFlowReport($normalizedFilters, $user),
            'loan-approval' => $this->buildLoanApprovalReport($normalizedFilters, $user),
            'transaction-report' => $this->buildTransactionReport($normalizedFilters, $user),
            'cashier-summary' => $this->buildCashierSummaryReport($normalizedFilters, $user),
            'member-account' => $this->buildMemberAccountReport($normalizedFilters, $user),
            'collection-monitoring' => $this->buildCollectionMonitoringReport($normalizedFilters, $user),
            'delinquent-accounts' => $this->buildDelinquentAccountsReport($normalizedFilters, $user),
            'delinquency' => $this->buildDelinquencyReport($normalizedFilters, $user),
            'loan-application' => $this->buildLoanApplicationReport($normalizedFilters, $user),
            'loan-evaluation' => $this->buildLoanEvaluationReport($normalizedFilters, $user),
            'approved-loans' => $this->buildApprovedLoansReport($normalizedFilters, $user),
            'restructured-loans' => $this->buildRestructuredLoansReport($normalizedFilters, $user),
            'loan-statement' => $this->buildLoanStatementReport($normalizedFilters, $user),
            'payment-history' => $this->buildPaymentHistoryReport($normalizedFilters, $user),
            'savings-statement' => $this->buildSavingsStatementReport($normalizedFilters, $user),
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

        if ($memberId) {
            $selectedMember = MemberDetail::query()
                ->select(['id', 'branch_id'])
                ->find($memberId);

            if ($selectedMember?->branch_id) {
                $branchId = (int) $selectedMember->branch_id;
            }
        }

        if ($user->isBranchScoped() && $user->branchId()) {
            $branchId = $user->branchId();
        }

        if ($user->isMember()) {
            $memberDetail = $user->profile?->memberDetail;

            if ($memberDetail) {
                $memberId = (int) $memberDetail->getKey();
                $branchId = $memberDetail->branch_id ? (int) $memberDetail->branch_id : $branchId;
            }
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

        $dailyEntriesQuery = $this->dailyCollectionEntryQuery($filters)
            ->with([
                'ao.profile',
                'ao.staffDetail.branch',
                'verifiedBy.profile',
            ])
            ->orderBy('collection_date', 'desc')
            ->orderBy('id', 'desc');

        if ($filters['member_id']) {
            // Daily collection entries are AO-level summaries and cannot be tied to a specific member.
            $dailyEntriesQuery->whereRaw('1 = 0');
        }

        $dailyEntries = $dailyEntriesQuery->get();

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

        $savingsTransactions = $this->savingsTransactionQuery($filters, true)->get();

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

        $savingsDeposits = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction));
        $savingsWithdrawals = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsWithdrawalAmount($transaction));
        $totalSavings = $savingsDeposits - $savingsWithdrawals;

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

    protected function buildFinancialSummaryReport(array $filters, User $user): array
    {
        $loanPayments = $this->dailyLoanPaymentQuery($filters)->get();
        $savingsTransactions = $this->savingsTransactionQuery($filters, true)->get();

        $loanReleases = $this->loanAccountQuery($filters)
            ->whereBetween('release_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->get(['principal_amount', 'net_release_amount', 'release_date']);

        $shareCapitalTransactions = $this->shareCapitalTransactionQuery($filters, true)->get();

        $deposits = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction));
        $withdrawals = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsWithdrawalAmount($transaction));

        $collections = (float) $loanPayments->sum('amount_paid');
        $interestIncome = (float) $loanPayments->sum('interest_paid');
        $penaltiesIncome = (float) $loanPayments->sum('penalty_paid');

        $loanReleaseAmount = $loanReleases->sum(function (LoanAccount $loanAccount): float {
            return (float) ($loanAccount->net_release_amount ?: $loanAccount->principal_amount);
        });

        $shareCredits = $shareCapitalTransactions
            ->filter(fn (object $transaction): bool => Str::lower((string) ($transaction->direction ?? '')) === 'credit')
            ->sum(fn (object $transaction): float => (float) ($transaction->amount ?? 0));

        $shareDebits = $shareCapitalTransactions
            ->filter(fn (object $transaction): bool => Str::lower((string) ($transaction->direction ?? '')) === 'debit')
            ->sum(fn (object $transaction): float => (float) ($transaction->amount ?? 0));

        $totalInflow = $collections + $deposits + $shareCredits;
        $totalOutflow = $withdrawals + $loanReleaseAmount + $shareDebits;
        $netMovement = $totalInflow - $totalOutflow;

        $paymentByMonth = $loanPayments->groupBy(fn (LoanPayment $payment): string => $payment->payment_date?->format('Y-m') ?? 'unknown');
        $savingsByMonth = $savingsTransactions->groupBy(fn (SavingsAccountTransaction $transaction): string => $this->savingsTransactionDate($transaction)->format('Y-m'));
        $loanReleasesByMonth = $loanReleases->groupBy(fn (LoanAccount $loanAccount): string => $loanAccount->release_date?->format('Y-m') ?? 'unknown');

        $monthlyRows = $this->monthBuckets($filters)
            ->map(function (array $month) use ($paymentByMonth, $savingsByMonth, $loanReleasesByMonth): array {
                /** @var Collection<int, LoanPayment> $monthlyPayments */
                $monthlyPayments = $paymentByMonth->get($month['key'], collect());
                /** @var Collection<int, SavingsAccountTransaction> $monthlySavings */
                $monthlySavings = $savingsByMonth->get($month['key'], collect());
                /** @var Collection<int, LoanAccount> $monthlyReleases */
                $monthlyReleases = $loanReleasesByMonth->get($month['key'], collect());

                $monthlyDeposits = $monthlySavings->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction));
                $monthlyWithdrawals = $monthlySavings->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsWithdrawalAmount($transaction));
                $monthlyCollections = (float) $monthlyPayments->sum('amount_paid');
                $monthlyReleasesAmount = $monthlyReleases->sum(function (LoanAccount $loanAccount): float {
                    return (float) ($loanAccount->net_release_amount ?: $loanAccount->principal_amount);
                });

                $monthlyInflow = $monthlyCollections + $monthlyDeposits;
                $monthlyOutflow = $monthlyWithdrawals + $monthlyReleasesAmount;

                return [
                    'month' => $month['label'],
                    'collections' => $this->money($monthlyCollections),
                    'deposits' => $this->money($monthlyDeposits),
                    'withdrawals' => $this->money($monthlyWithdrawals),
                    'loan_releases' => $this->money($monthlyReleasesAmount),
                    'net_cash' => $this->money($monthlyInflow - $monthlyOutflow),
                ];
            })
            ->values();

        $mainRows = [
            ['metric' => 'Collections (Loan Payments)', 'value' => $this->money($collections)],
            ['metric' => 'Savings Deposits', 'value' => $this->money($deposits)],
            ['metric' => 'Share Capital Credits', 'value' => $this->money($shareCredits)],
            ['metric' => 'Total Inflow', 'value' => $this->money($totalInflow)],
            ['metric' => 'Savings Withdrawals', 'value' => $this->money($withdrawals)],
            ['metric' => 'Loan Releases', 'value' => $this->money($loanReleaseAmount)],
            ['metric' => 'Share Capital Debits', 'value' => $this->money($shareDebits)],
            ['metric' => 'Total Outflow', 'value' => $this->money($totalOutflow)],
            ['metric' => 'Net Movement', 'value' => $this->money($netMovement)],
            ['metric' => 'Interest Income', 'value' => $this->money($interestIncome)],
            ['metric' => 'Penalties Income', 'value' => $this->money($penaltiesIncome)],
        ];

        return $this->baseReportPayload(
            title: 'Financial Summary Report',
            subtitle: 'Consolidated inflow, outflow, and net movement across loans, savings, and share capital.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Total Inflow', 'value' => $this->money($totalInflow)],
                ['label' => 'Total Outflow', 'value' => $this->money($totalOutflow)],
                ['label' => 'Net Movement', 'value' => $this->money($netMovement)],
                ['label' => 'Interest Income', 'value' => $this->money($interestIncome)],
                ['label' => 'Penalties', 'value' => $this->money($penaltiesIncome)],
            ],
            mainTable: [
                'title' => 'Financial Highlights',
                'columns' => [
                    ['key' => 'metric', 'label' => 'Metric'],
                    ['key' => 'value', 'label' => 'Value', 'align' => 'right'],
                ],
                'rows' => $mainRows,
            ],
            sections: [
                [
                    'title' => 'Monthly Cash Movement',
                    'columns' => [
                        ['key' => 'month', 'label' => 'Month'],
                        ['key' => 'collections', 'label' => 'Collections', 'align' => 'right'],
                        ['key' => 'deposits', 'label' => 'Deposits', 'align' => 'right'],
                        ['key' => 'withdrawals', 'label' => 'Withdrawals', 'align' => 'right'],
                        ['key' => 'loan_releases', 'label' => 'Loan Releases', 'align' => 'right'],
                        ['key' => 'net_cash', 'label' => 'Net Cash', 'align' => 'right'],
                    ],
                    'rows' => $monthlyRows,
                    'totals_label' => 'Totals',
                    'totals' => [
                        'collections' => $this->money($collections),
                        'deposits' => $this->money($deposits),
                        'withdrawals' => $this->money($withdrawals),
                        'loan_releases' => $this->money($loanReleaseAmount),
                        'net_cash' => $this->money($netMovement),
                    ],
                ],
            ],
        );
    }

    protected function buildAuditTrailReport(array $filters, User $user): array
    {
        $loanStatusLogs = LoanApplicationStatusLog::query()
            ->with(['application.member.profile'])
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('application.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('application.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereBetween('changed_at', [$filters['start'], $filters['end']])
            ->orderByDesc('changed_at')
            ->limit(500)
            ->get();

        $restructureStatusLogs = RestructureApplicationStatusLog::query()
            ->with([
                'restructureApplication.loanApplication.member.profile',
                'restructureApplication.oldLoanAccount',
            ])
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('restructureApplication.loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('restructureApplication.loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereBetween('changed_at', [$filters['start'], $filters['end']])
            ->orderByDesc('changed_at')
            ->limit(500)
            ->get();

        $paymentAuditLogs = DB::table('loan_payment_audit_logs as logs')
            ->leftJoin('loan_payments as payment', 'payment.loan_payment_id', '=', 'logs.loan_payment_id')
            ->leftJoin('loan_applications as application', 'application.loan_application_id', '=', 'payment.loan_application_id')
            ->leftJoin('member_details as member', 'member.id', '=', 'application.member_id')
            ->leftJoin('profiles as profile', 'profile.profile_id', '=', 'member.profile_id')
            ->when($filters['member_id'], fn (QueryBuilder $query, int $memberId) => $query->where('member.id', $memberId))
            ->when($filters['branch_id'], fn (QueryBuilder $query, int $branchId) => $query->where('member.branch_id', $branchId))
            ->whereBetween('logs.created_at', [$filters['start'], $filters['end']])
            ->orderByDesc('logs.created_at')
            ->limit(500)
            ->get([
                'logs.id',
                'logs.loan_payment_id',
                'logs.user_id',
                'logs.action',
                'logs.reason',
                'logs.created_at',
                'application.loan_application_id',
                'profile.first_name',
                'profile.middle_name',
                'profile.last_name',
            ]);

        $actorIds = collect()
            ->merge($loanStatusLogs->pluck('changed_by_user_id'))
            ->merge($restructureStatusLogs->pluck('changed_by_user_id'))
            ->merge($paymentAuditLogs->pluck('user_id'))
            ->filter()
            ->unique()
            ->values();

        $actorNames = $this->mapActorNames($actorIds);

        $rows = collect();

        foreach ($loanStatusLogs as $log) {
            $application = $log->application;
            $memberName = $application?->member?->profile?->full_name ?? 'Unknown Member';
            $fromStatus = $log->from_status ?? '—';
            $toStatus = $log->to_status ?? '—';

            $rows->push([
                'sort_at' => Carbon::parse($log->changed_at),
                'timestamp' => Carbon::parse($log->changed_at)->format('M d, Y h:i A'),
                'event_type' => 'Loan Application',
                'reference' => 'App #'.($log->loan_application_id ?? '—'),
                'member' => $memberName,
                'action' => $fromStatus.' -> '.$toStatus,
                'actor' => $this->actorName($log->changed_by_user_id ? (int) $log->changed_by_user_id : null, $actorNames),
                'reason' => $log->reason ?: '—',
            ]);
        }

        foreach ($restructureStatusLogs as $log) {
            $restructureApplication = $log->restructureApplication;
            $memberName = $restructureApplication?->loanApplication?->member?->profile?->full_name ?? 'Unknown Member';
            $reference = 'Restructure #'.($log->restructure_application_id ?? '—');

            if ($restructureApplication?->old_loan_account_id) {
                $reference .= ' (Old Loan #'.$restructureApplication->old_loan_account_id.')';
            }

            $rows->push([
                'sort_at' => Carbon::parse($log->changed_at),
                'timestamp' => Carbon::parse($log->changed_at)->format('M d, Y h:i A'),
                'event_type' => 'Restructure',
                'reference' => $reference,
                'member' => $memberName,
                'action' => ($log->from_status ?? '—').' -> '.($log->to_status ?? '—'),
                'actor' => $this->actorName($log->changed_by_user_id ? (int) $log->changed_by_user_id : null, $actorNames),
                'reason' => $log->reason ?: '—',
            ]);
        }

        foreach ($paymentAuditLogs as $log) {
            $memberName = trim(implode(' ', array_filter([
                $log->first_name,
                $log->middle_name,
                $log->last_name,
            ]))) ?: 'Unknown Member';

            $rows->push([
                'sort_at' => Carbon::parse($log->created_at),
                'timestamp' => Carbon::parse($log->created_at)->format('M d, Y h:i A'),
                'event_type' => 'Loan Payment',
                'reference' => 'Payment #'.($log->loan_payment_id ?? '—'),
                'member' => $memberName,
                'action' => Str::upper((string) $log->action),
                'actor' => $this->actorName($log->user_id ? (int) $log->user_id : null, $actorNames),
                'reason' => $log->reason ?: '—',
            ]);
        }

        $rows = $rows
            ->sortByDesc('sort_at')
            ->values()
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            });

        $actionRows = $rows
            ->groupBy('action')
            ->map(function (Collection $group, string $action): array {
                return [
                    'action' => $action,
                    'entries' => number_format($group->count()),
                ];
            })
            ->sortByDesc(fn (array $row): int => (int) str_replace(',', '', $row['entries']))
            ->values();

        return $this->baseReportPayload(
            title: 'Audit Trail Report',
            subtitle: 'Status transitions and payment-level audit entries across loan workflows.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Total Audit Entries', 'value' => number_format($rows->count())],
                ['label' => 'Loan Application Logs', 'value' => number_format($loanStatusLogs->count())],
                ['label' => 'Restructure Logs', 'value' => number_format($restructureStatusLogs->count())],
                ['label' => 'Payment Audit Logs', 'value' => number_format($paymentAuditLogs->count())],
                ['label' => 'Unique Actors', 'value' => number_format(count($actorNames))],
            ],
            mainTable: [
                'title' => 'Audit Entries',
                'columns' => [
                    ['key' => 'timestamp', 'label' => 'Timestamp'],
                    ['key' => 'event_type', 'label' => 'Event Type'],
                    ['key' => 'reference', 'label' => 'Reference'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'action', 'label' => 'Action'],
                    ['key' => 'actor', 'label' => 'Actor'],
                    ['key' => 'reason', 'label' => 'Reason'],
                ],
                'rows' => $rows,
            ],
            sections: [
                [
                    'title' => 'Action Breakdown',
                    'columns' => [
                        ['key' => 'action', 'label' => 'Action'],
                        ['key' => 'entries', 'label' => 'Entries', 'align' => 'right'],
                    ],
                    'rows' => $actionRows,
                    'totals_label' => 'Total',
                    'totals' => [
                        'entries' => number_format($rows->count()),
                    ],
                ],
            ],
        );
    }

    protected function buildBranchPerformanceReport(array $filters, User $user): array
    {
        $branches = Branch::query()
            ->when($filters['branch_id'], fn (Builder $query, int $branchId) => $query->whereKey($branchId))
            ->orderBy('name')
            ->get(['branch_id', 'name']);

        $memberSummary = $this->memberScopeQuery($filters)
            ->selectRaw('branch_id, COUNT(*) as total_members, SUM(CASE WHEN status = "Active" THEN 1 ELSE 0 END) as active_members')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $loanSummary = LoanAccount::query()
            ->join('loan_applications as application', 'application.loan_application_id', '=', 'loan_accounts.loan_application_id')
            ->join('member_details as member', 'member.id', '=', 'application.member_id')
            ->when($filters['member_id'], fn (Builder $query, int $memberId) => $query->where('member.id', $memberId))
            ->when($filters['branch_id'], fn (Builder $query, int $branchId) => $query->where('member.branch_id', $branchId))
            ->whereDate('loan_accounts.release_date', '<=', $filters['end']->toDateString())
            ->groupBy('member.branch_id')
            ->get([
                DB::raw('member.branch_id as branch_id'),
                DB::raw('SUM(CASE WHEN loan_accounts.status = "Active" THEN 1 ELSE 0 END) as active_loans'),
                DB::raw('SUM(CASE WHEN loan_accounts.status IN ("Active", "Restructured") THEN loan_accounts.balance ELSE 0 END) as outstanding_balance'),
            ])
            ->keyBy('branch_id');

        $collectionSummary = LoanPayment::query()
            ->join('loan_applications as application', 'application.loan_application_id', '=', 'loan_payments.loan_application_id')
            ->join('member_details as member', 'member.id', '=', 'application.member_id')
            ->where('loan_payments.status', 'Posted')
            ->when($filters['member_id'], fn (Builder $query, int $memberId) => $query->where('member.id', $memberId))
            ->when($filters['branch_id'], fn (Builder $query, int $branchId) => $query->where('member.branch_id', $branchId))
            ->whereBetween('loan_payments.payment_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->groupBy('member.branch_id')
            ->get([
                DB::raw('member.branch_id as branch_id'),
                DB::raw('COUNT(loan_payments.loan_payment_id) as transaction_count'),
                DB::raw('SUM(loan_payments.amount_paid) as collections'),
                DB::raw('SUM(loan_payments.interest_paid) as interest_collected'),
                DB::raw('SUM(loan_payments.penalty_paid) as penalties_collected'),
            ])
            ->keyBy('branch_id');

        $delinquencyByBranch = [];

        $delinquencyAccounts = $this->loanAccountQuery($filters)
            ->where('status', 'Active')
            ->with([
                'loanApplication.member.profile',
                'profile.memberDetail.branch',
            ])
            ->get();

        foreach ($delinquencyAccounts as $loanAccount) {
            $summary = $this->summarizeLoanSchedule($this->loanScheduleService->build($loanAccount));

            if ($summary['days_overdue'] <= 0) {
                continue;
            }

            $branchId = $loanAccount->loanApplication?->member?->branch_id
                ?? $loanAccount->profile?->memberDetail?->branch_id;

            if (! $branchId) {
                continue;
            }

            if (! isset($delinquencyByBranch[$branchId])) {
                $delinquencyByBranch[$branchId] = [
                    'overdue_accounts' => 0,
                    'overdue_balance' => 0.0,
                ];
            }

            $delinquencyByBranch[$branchId]['overdue_accounts']++;
            $delinquencyByBranch[$branchId]['overdue_balance'] += (float) $loanAccount->balance;
        }

        $branchIds = $branches->pluck('branch_id')
            ->merge($memberSummary->keys())
            ->merge($loanSummary->keys())
            ->merge($collectionSummary->keys())
            ->merge(collect(array_keys($delinquencyByBranch)))
            ->filter()
            ->unique()
            ->values();

        $branchNameMap = $branches->mapWithKeys(fn (Branch $branch): array => [$branch->branch_id => $branch->name]);

        if ($branchIds->isNotEmpty()) {
            $fallbackNames = Branch::query()
                ->whereIn('branch_id', $branchIds->all())
                ->pluck('name', 'branch_id');

            foreach ($fallbackNames as $branchId => $name) {
                $branchNameMap[$branchId] = $name;
            }
        }

        $rows = $branchIds->map(function (int $branchId) use ($branchNameMap, $memberSummary, $loanSummary, $collectionSummary, $delinquencyByBranch): array {
            $memberData = $memberSummary->get($branchId);
            $loanData = $loanSummary->get($branchId);
            $collectionData = $collectionSummary->get($branchId);
            $delinquencyData = $delinquencyByBranch[$branchId] ?? ['overdue_accounts' => 0, 'overdue_balance' => 0.0];

            $activeLoans = (int) ($loanData?->active_loans ?? 0);
            $overdueAccounts = (int) ($delinquencyData['overdue_accounts'] ?? 0);
            $delinquencyRate = $activeLoans > 0
                ? ($overdueAccounts / $activeLoans) * 100
                : 0;

            return [
                'branch' => $branchNameMap[$branchId] ?? 'Branch #'.$branchId,
                'members' => number_format((int) ($memberData?->total_members ?? 0)),
                'active_members' => number_format((int) ($memberData?->active_members ?? 0)),
                'active_loans' => number_format($activeLoans),
                'collections' => $this->money((float) ($collectionData?->collections ?? 0)),
                'outstanding_balance' => $this->money((float) ($loanData?->outstanding_balance ?? 0)),
                'overdue_accounts' => number_format($overdueAccounts),
                'overdue_balance' => $this->money((float) ($delinquencyData['overdue_balance'] ?? 0)),
                'delinquency_rate' => $this->formatPercent($delinquencyRate),
            ];
        })
            ->sortBy('branch')
            ->values();

        $totalCollections = $rows->sum(fn (array $row): float => $this->parseMoney($row['collections']));
        $totalOutstanding = $rows->sum(fn (array $row): float => $this->parseMoney($row['outstanding_balance']));
        $totalOverdueBalance = $rows->sum(fn (array $row): float => $this->parseMoney($row['overdue_balance']));

        $bestCollectionBranch = $rows->sortByDesc(fn (array $row): float => $this->parseMoney($row['collections']))->first();

        return $this->baseReportPayload(
            title: 'Branch Performance Report',
            subtitle: 'Branch-level comparison of membership, collections, portfolio, and delinquency indicators.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Branches in Scope', 'value' => number_format($rows->count())],
                ['label' => 'Total Collections', 'value' => $this->money($totalCollections)],
                ['label' => 'Outstanding Portfolio', 'value' => $this->money($totalOutstanding)],
                ['label' => 'Overdue Balance', 'value' => $this->money($totalOverdueBalance)],
                ['label' => 'Top Collection Branch', 'value' => $bestCollectionBranch['branch'] ?? '—'],
            ],
            mainTable: [
                'title' => 'Branch KPIs',
                'columns' => [
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'members', 'label' => 'Members', 'align' => 'right'],
                    ['key' => 'active_members', 'label' => 'Active Members', 'align' => 'right'],
                    ['key' => 'active_loans', 'label' => 'Active Loans', 'align' => 'right'],
                    ['key' => 'collections', 'label' => 'Collections', 'align' => 'right'],
                    ['key' => 'outstanding_balance', 'label' => 'Outstanding', 'align' => 'right'],
                    ['key' => 'overdue_accounts', 'label' => 'Overdue Accts', 'align' => 'right'],
                    ['key' => 'overdue_balance', 'label' => 'Overdue Balance', 'align' => 'right'],
                    ['key' => 'delinquency_rate', 'label' => 'Delinquency Rate', 'align' => 'right'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'members' => number_format($rows->sum(fn (array $row): int => (int) str_replace(',', '', $row['members']))),
                    'active_members' => number_format($rows->sum(fn (array $row): int => (int) str_replace(',', '', $row['active_members']))),
                    'active_loans' => number_format($rows->sum(fn (array $row): int => (int) str_replace(',', '', $row['active_loans']))),
                    'collections' => $this->money($totalCollections),
                    'outstanding_balance' => $this->money($totalOutstanding),
                    'overdue_accounts' => number_format($rows->sum(fn (array $row): int => (int) str_replace(',', '', $row['overdue_accounts']))),
                    'overdue_balance' => $this->money($totalOverdueBalance),
                ],
            ],
        );
    }

    protected function buildLoanPortfolioReport(array $filters, User $user): array
    {
        $loanAccounts = $this->loanAccountQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
                'profile.memberDetail.branch',
            ])
            ->orderByDesc('release_date')
            ->orderByDesc('loan_account_id')
            ->get();

        $rows = $loanAccounts->map(function (LoanAccount $loanAccount): array {
            return [
                'loan_reference' => 'Loan #'.$loanAccount->loan_account_id,
                'member' => $loanAccount->loanApplication?->member?->profile?->full_name
                    ?? $loanAccount->profile?->full_name
                    ?? 'Unknown Member',
                'branch' => $loanAccount->loanApplication?->member?->branch?->name
                    ?? $loanAccount->profile?->memberDetail?->branch?->name
                    ?? '—',
                'loan_type' => $loanAccount->loanApplication?->type?->name ?? '—',
                'status' => $loanAccount->status ?? '—',
                'release_date' => $loanAccount->release_date?->format('M d, Y') ?? '—',
                'maturity_date' => $loanAccount->maturity_date?->format('M d, Y') ?? '—',
                'principal_amount' => $this->money($loanAccount->principal_amount),
                'balance' => $this->money($loanAccount->balance),
                'interest_rate' => number_format((float) $loanAccount->interest_rate, 2).'%',
            ];
        })->values();

        $typeRows = $loanAccounts
            ->groupBy(fn (LoanAccount $loanAccount): string => $loanAccount->loanApplication?->type?->name ?? 'Uncategorized')
            ->map(function (Collection $group, string $loanType): array {
                return [
                    'loan_type' => $loanType,
                    'accounts' => number_format($group->count()),
                    'principal_amount' => $this->money($group->sum('principal_amount')),
                    'balance' => $this->money($group->sum('balance')),
                ];
            })
            ->sortByDesc(fn (array $row): float => $this->parseMoney($row['balance']))
            ->values();

        $statusRows = $loanAccounts
            ->groupBy(fn (LoanAccount $loanAccount): string => $loanAccount->status ?? 'Unknown')
            ->map(function (Collection $group, string $status): array {
                return [
                    'status' => $status,
                    'accounts' => number_format($group->count()),
                    'principal_amount' => $this->money($group->sum('principal_amount')),
                    'balance' => $this->money($group->sum('balance')),
                ];
            })
            ->sortByDesc(fn (array $row): float => $this->parseMoney($row['balance']))
            ->values();

        $outstanding = (float) $loanAccounts->whereIn('status', ['Active', 'Restructured'])->sum('balance');

        return $this->baseReportPayload(
            title: 'Loan Portfolio Report',
            subtitle: 'Loan portfolio composition by account, type, and status.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Accounts', 'value' => number_format($loanAccounts->count())],
                ['label' => 'Principal Booked', 'value' => $this->money($loanAccounts->sum('principal_amount'))],
                ['label' => 'Outstanding Balance', 'value' => $this->money($outstanding)],
                ['label' => 'Completed Loans', 'value' => number_format($loanAccounts->where('status', 'Completed')->count())],
                ['label' => 'Defaulted Loans', 'value' => number_format($loanAccounts->where('status', 'Defaulted')->count())],
            ],
            mainTable: [
                'title' => 'Loan Portfolio Details',
                'columns' => [
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'release_date', 'label' => 'Release Date'],
                    ['key' => 'maturity_date', 'label' => 'Maturity Date'],
                    ['key' => 'interest_rate', 'label' => 'Rate', 'align' => 'right'],
                    ['key' => 'principal_amount', 'label' => 'Principal', 'align' => 'right'],
                    ['key' => 'balance', 'label' => 'Balance', 'align' => 'right'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'principal_amount' => $this->money($loanAccounts->sum('principal_amount')),
                    'balance' => $this->money($loanAccounts->sum('balance')),
                ],
            ],
            sections: [
                [
                    'title' => 'Portfolio by Loan Type',
                    'columns' => [
                        ['key' => 'loan_type', 'label' => 'Loan Type'],
                        ['key' => 'accounts', 'label' => 'Accounts', 'align' => 'right'],
                        ['key' => 'principal_amount', 'label' => 'Principal', 'align' => 'right'],
                        ['key' => 'balance', 'label' => 'Balance', 'align' => 'right'],
                    ],
                    'rows' => $typeRows,
                ],
                [
                    'title' => 'Portfolio by Status',
                    'columns' => [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'accounts', 'label' => 'Accounts', 'align' => 'right'],
                        ['key' => 'principal_amount', 'label' => 'Principal', 'align' => 'right'],
                        ['key' => 'balance', 'label' => 'Balance', 'align' => 'right'],
                    ],
                    'rows' => $statusRows,
                ],
            ],
        );
    }

    protected function buildCashFlowReport(array $filters, User $user): array
    {
        $loanPayments = $this->dailyLoanPaymentQuery($filters)->get();
        $savingsTransactions = $this->savingsTransactionQuery($filters, true)->get();
        $loanReleases = $this->loanAccountQuery($filters)
            ->whereBetween('release_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->get(['principal_amount', 'net_release_amount', 'release_date']);

        $paymentByMonth = $loanPayments->groupBy(fn (LoanPayment $payment): string => $payment->payment_date?->format('Y-m') ?? 'unknown');
        $savingsByMonth = $savingsTransactions->groupBy(fn (SavingsAccountTransaction $transaction): string => $this->savingsTransactionDate($transaction)->format('Y-m'));
        $releaseByMonth = $loanReleases->groupBy(fn (LoanAccount $loanAccount): string => $loanAccount->release_date?->format('Y-m') ?? 'unknown');

        $rows = $this->monthBuckets($filters)
            ->map(function (array $month) use ($paymentByMonth, $savingsByMonth, $releaseByMonth): array {
                /** @var Collection<int, LoanPayment> $payments */
                $payments = $paymentByMonth->get($month['key'], collect());
                /** @var Collection<int, SavingsAccountTransaction> $savings */
                $savings = $savingsByMonth->get($month['key'], collect());
                /** @var Collection<int, LoanAccount> $releases */
                $releases = $releaseByMonth->get($month['key'], collect());

                $collections = (float) $payments->sum('amount_paid');
                $interest = (float) $payments->sum('interest_paid');
                $penalties = (float) $payments->sum('penalty_paid');
                $deposits = $savings->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction));
                $withdrawals = $savings->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsWithdrawalAmount($transaction));
                $released = $releases->sum(function (LoanAccount $loanAccount): float {
                    return (float) ($loanAccount->net_release_amount ?: $loanAccount->principal_amount);
                });

                $inflow = $collections + $deposits;
                $outflow = $withdrawals + $released;

                return [
                    'month' => $month['label'],
                    'collections' => $this->money($collections),
                    'interest' => $this->money($interest),
                    'penalties' => $this->money($penalties),
                    'deposits' => $this->money($deposits),
                    'withdrawals' => $this->money($withdrawals),
                    'loan_releases' => $this->money($released),
                    'net_cash' => $this->money($inflow - $outflow),
                ];
            })
            ->values();

        $totalCollections = (float) $loanPayments->sum('amount_paid');
        $totalInterest = (float) $loanPayments->sum('interest_paid');
        $totalPenalties = (float) $loanPayments->sum('penalty_paid');
        $totalDeposits = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction));
        $totalWithdrawals = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsWithdrawalAmount($transaction));
        $totalReleases = $loanReleases->sum(function (LoanAccount $loanAccount): float {
            return (float) ($loanAccount->net_release_amount ?: $loanAccount->principal_amount);
        });

        $netCash = ($totalCollections + $totalDeposits) - ($totalWithdrawals + $totalReleases);

        return $this->baseReportPayload(
            title: 'Cash Flow Report',
            subtitle: 'Monthly movement of collections, savings, and loan releases for treasury oversight.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Collections', 'value' => $this->money($totalCollections)],
                ['label' => 'Deposits', 'value' => $this->money($totalDeposits)],
                ['label' => 'Withdrawals', 'value' => $this->money($totalWithdrawals)],
                ['label' => 'Loan Releases', 'value' => $this->money($totalReleases)],
                ['label' => 'Net Cash', 'value' => $this->money($netCash)],
            ],
            mainTable: [
                'title' => 'Monthly Cash Flow',
                'columns' => [
                    ['key' => 'month', 'label' => 'Month'],
                    ['key' => 'collections', 'label' => 'Collections', 'align' => 'right'],
                    ['key' => 'interest', 'label' => 'Interest', 'align' => 'right'],
                    ['key' => 'penalties', 'label' => 'Penalties', 'align' => 'right'],
                    ['key' => 'deposits', 'label' => 'Deposits', 'align' => 'right'],
                    ['key' => 'withdrawals', 'label' => 'Withdrawals', 'align' => 'right'],
                    ['key' => 'loan_releases', 'label' => 'Loan Releases', 'align' => 'right'],
                    ['key' => 'net_cash', 'label' => 'Net Cash', 'align' => 'right'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'collections' => $this->money($totalCollections),
                    'interest' => $this->money($totalInterest),
                    'penalties' => $this->money($totalPenalties),
                    'deposits' => $this->money($totalDeposits),
                    'withdrawals' => $this->money($totalWithdrawals),
                    'loan_releases' => $this->money($totalReleases),
                    'net_cash' => $this->money($netCash),
                ],
            ],
        );
    }

    protected function buildLoanApprovalReport(array $filters, User $user): array
    {
        $applications = $this->loanApplicationQuery($filters, true)
            ->with([
                'member.profile',
                'type',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('loan_application_id')
            ->get();

        $approverIds = $applications
            ->pluck('manager_approved_by_user_id')
            ->merge($applications->pluck('admin_approved_by_user_id'))
            ->filter()
            ->unique()
            ->values();

        $approvers = $this->mapActorNames($approverIds);
        $approvalLimit = (float) CoopSetting::get('loan.loan_officer_approval_limit', 20000.00);

        $rows = $applications->map(function (LoanApplication $application) use ($approvers, $approvalLimit): array {
            $submittedAt = $this->loanApplicationDate($application);

            return [
                'application_no' => 'App #'.$application->loan_application_id,
                'member' => $application->member?->profile?->full_name ?? 'Unknown Member',
                'branch' => $application->member?->branch?->name ?? '—',
                'loan_type' => $application->type?->name ?? '—',
                'amount_requested' => $this->money($application->amount_requested),
                'status' => $application->status,
                'submitted_at' => $submittedAt->format('M d, Y'),
                'manager_approval' => $application->manager_approved_at
                    ? ($this->actorName($application->manager_approved_by_user_id ? (int) $application->manager_approved_by_user_id : null, $approvers, 'Manager').' ('.$application->manager_approved_at->format('M d').')')
                    : 'Pending',
                'admin_approval' => $application->admin_approved_at
                    ? ($this->actorName($application->admin_approved_by_user_id ? (int) $application->admin_approved_by_user_id : null, $approvers, 'Admin').' ('.$application->admin_approved_at->format('M d').')')
                    : 'Pending',
                'stage' => $this->loanApprovalStage($application),
                'within_limit' => (float) $application->amount_requested <= $approvalLimit ? 'Yes' : 'No',
            ];
        })->values();

        $statusRows = $applications
            ->groupBy(fn (LoanApplication $application): string => $application->status)
            ->map(function (Collection $group, string $status): array {
                return [
                    'status' => $status,
                    'count' => number_format($group->count()),
                    'amount_requested' => $this->money($group->sum('amount_requested')),
                ];
            })
            ->sortByDesc(fn (array $row): int => (int) str_replace(',', '', $row['count']))
            ->values();

        $managerApproved = $applications->filter(fn (LoanApplication $application): bool => $application->manager_approved_at !== null)->count();
        $finalApproved = $applications->filter(fn (LoanApplication $application): bool => $application->admin_approved_at !== null)->count();

        return $this->baseReportPayload(
            title: 'Loan Approval Report',
            subtitle: 'Approval pipeline visibility from submission through manager and admin decision points.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Applications', 'value' => number_format($applications->count())],
                ['label' => 'Requested Amount', 'value' => $this->money($applications->sum('amount_requested'))],
                ['label' => 'Manager Approved', 'value' => number_format($managerApproved)],
                ['label' => 'Final Approved', 'value' => number_format($finalApproved)],
                ['label' => 'Loan Officer Limit', 'value' => $this->money($approvalLimit)],
            ],
            mainTable: [
                'title' => 'Loan Approval Pipeline',
                'columns' => [
                    ['key' => 'application_no', 'label' => 'Application'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'amount_requested', 'label' => 'Requested', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'submitted_at', 'label' => 'Submitted'],
                    ['key' => 'manager_approval', 'label' => 'Manager'],
                    ['key' => 'admin_approval', 'label' => 'Admin'],
                    ['key' => 'within_limit', 'label' => 'Within Limit'],
                    ['key' => 'stage', 'label' => 'Current Stage'],
                ],
                'rows' => $rows,
            ],
            sections: [
                [
                    'title' => 'Status Breakdown',
                    'columns' => [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'count', 'label' => 'Count', 'align' => 'right'],
                        ['key' => 'amount_requested', 'label' => 'Requested Amount', 'align' => 'right'],
                    ],
                    'rows' => $statusRows,
                ],
            ],
        );
    }

    protected function buildTransactionReport(array $filters, User $user): array
    {
        $loanPayments = $this->dailyLoanPaymentQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanAccount.profile',
                'postedBy.profile',
            ])
            ->get();

        $collectionPostings = $this->dailyCollectionPostingQuery($filters)
            ->with([
                'loanAccount.profile.memberDetail.branch',
                'postedBy.profile',
            ])
            ->get();

        $savingsTransactions = $this->savingsTransactionQuery($filters, true)
            ->with([
                'member.memberDetail.branch',
                'postedBy.profile',
            ])
            ->get();

        $rows = collect();

        foreach ($loanPayments as $payment) {
            $rows->push([
                'sort_at' => $payment->payment_date ?? $payment->created_at,
                'date' => $payment->payment_date?->format('M d, Y') ?? '—',
                'module' => 'Loan Payment',
                'member' => $payment->loanApplication?->member?->profile?->full_name
                    ?? $payment->loanAccount?->profile?->full_name
                    ?? 'Unknown Member',
                'reference' => $payment->loanAccount?->loan_account_id
                    ? 'Loan #'.$payment->loanAccount->loan_account_id
                    : 'Loan App #'.$payment->loan_application_id,
                'direction' => 'Inflow',
                'amount' => $this->money($payment->amount_paid),
                'status' => $payment->status ?? '—',
                'posted_by' => $payment->postedBy?->name ?? 'System',
            ]);
        }

        foreach ($collectionPostings as $posting) {
            $rows->push([
                'sort_at' => $posting->payment_date ?? $posting->created_at,
                'date' => $posting->payment_date?->format('M d, Y') ?? '—',
                'module' => 'Collection Posting',
                'member' => $posting->loanAccount?->profile?->full_name
                    ?? $posting->member_name
                    ?? 'Unknown Member',
                'reference' => $posting->reference_number ?: ('Posting #'.$posting->id),
                'direction' => 'Inflow',
                'amount' => $this->money($posting->amount_paid),
                'status' => $posting->status ?? '—',
                'posted_by' => $posting->postedBy?->name ?? 'System',
            ]);
        }

        foreach ($savingsTransactions as $transaction) {
            $depositAmount = $this->savingsDepositAmount($transaction);
            $withdrawalAmount = $this->savingsWithdrawalAmount($transaction);
            $amount = $depositAmount > 0 ? $depositAmount : $withdrawalAmount;

            $rows->push([
                'sort_at' => $this->savingsTransactionDate($transaction),
                'date' => $this->savingsTransactionDate($transaction)->format('M d, Y'),
                'module' => 'Savings Transaction',
                'member' => $transaction->member?->full_name ?? 'Unknown Member',
                'reference' => $transaction->reference_no ?: ('Savings Tx #'.$transaction->id),
                'direction' => $depositAmount > 0 ? 'Inflow' : 'Outflow',
                'amount' => $this->money($amount),
                'status' => $transaction->status ?? '—',
                'posted_by' => $transaction->postedBy?->name ?? 'System',
            ]);
        }

        $rows = $rows
            ->sortByDesc('sort_at')
            ->values()
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            });

        $moduleRows = $rows
            ->groupBy('module')
            ->map(function (Collection $group, string $module): array {
                $inflow = $group
                    ->filter(fn (array $row): bool => $row['direction'] === 'Inflow')
                    ->sum(fn (array $row): float => $this->parseMoney($row['amount']));

                $outflow = $group
                    ->filter(fn (array $row): bool => $row['direction'] === 'Outflow')
                    ->sum(fn (array $row): float => $this->parseMoney($row['amount']));

                return [
                    'module' => $module,
                    'entries' => number_format($group->count()),
                    'inflow' => $this->money($inflow),
                    'outflow' => $this->money($outflow),
                    'net' => $this->money($inflow - $outflow),
                ];
            })
            ->values();

        $totalInflow = $rows
            ->filter(fn (array $row): bool => $row['direction'] === 'Inflow')
            ->sum(fn (array $row): float => $this->parseMoney($row['amount']));

        $totalOutflow = $rows
            ->filter(fn (array $row): bool => $row['direction'] === 'Outflow')
            ->sum(fn (array $row): float => $this->parseMoney($row['amount']));

        return $this->baseReportPayload(
            title: 'Transaction Report',
            subtitle: 'Unified posting log across loan, collection, and savings transactions.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Total Entries', 'value' => number_format($rows->count())],
                ['label' => 'Total Inflow', 'value' => $this->money($totalInflow)],
                ['label' => 'Total Outflow', 'value' => $this->money($totalOutflow)],
                ['label' => 'Net', 'value' => $this->money($totalInflow - $totalOutflow)],
            ],
            mainTable: [
                'title' => 'Transaction Ledger',
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'module', 'label' => 'Module'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'reference', 'label' => 'Reference'],
                    ['key' => 'direction', 'label' => 'Direction'],
                    ['key' => 'amount', 'label' => 'Amount', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'posted_by', 'label' => 'Posted By'],
                ],
                'rows' => $rows,
            ],
            sections: [
                [
                    'title' => 'Module Summary',
                    'columns' => [
                        ['key' => 'module', 'label' => 'Module'],
                        ['key' => 'entries', 'label' => 'Entries', 'align' => 'right'],
                        ['key' => 'inflow', 'label' => 'Inflow', 'align' => 'right'],
                        ['key' => 'outflow', 'label' => 'Outflow', 'align' => 'right'],
                        ['key' => 'net', 'label' => 'Net', 'align' => 'right'],
                    ],
                    'rows' => $moduleRows,
                ],
            ],
        );
    }

    protected function buildCashierSummaryReport(array $filters, User $user): array
    {
        $loanPayments = $this->dailyLoanPaymentQuery($filters)
            ->with('postedBy.profile')
            ->get();

        $savingsTransactions = $this->savingsTransactionQuery($filters, true)
            ->with('postedBy.profile')
            ->get();

        $daily = [];

        foreach ($loanPayments as $payment) {
            $dateKey = $payment->payment_date?->toDateString() ?? now()->toDateString();
            $userId = $payment->posted_by ? (int) $payment->posted_by : 0;
            $cashierName = $payment->postedBy?->name ?? 'System';
            $key = $userId.'|'.$dateKey;

            if (! isset($daily[$key])) {
                $daily[$key] = [
                    'cashier' => $cashierName,
                    'date' => Carbon::parse($dateKey),
                    'transactions' => 0,
                    'loan_collections' => 0.0,
                    'savings_deposits' => 0.0,
                    'savings_withdrawals' => 0.0,
                ];
            }

            $daily[$key]['transactions']++;
            $daily[$key]['loan_collections'] += (float) $payment->amount_paid;
        }

        foreach ($savingsTransactions as $transaction) {
            $date = $this->savingsTransactionDate($transaction);
            $dateKey = $date->toDateString();
            $userId = $transaction->posted_by_user_id ? (int) $transaction->posted_by_user_id : 0;
            $cashierName = $transaction->postedBy?->name ?? 'System';
            $key = $userId.'|'.$dateKey;

            if (! isset($daily[$key])) {
                $daily[$key] = [
                    'cashier' => $cashierName,
                    'date' => $date,
                    'transactions' => 0,
                    'loan_collections' => 0.0,
                    'savings_deposits' => 0.0,
                    'savings_withdrawals' => 0.0,
                ];
            }

            $daily[$key]['transactions']++;
            $daily[$key]['savings_deposits'] += $this->savingsDepositAmount($transaction);
            $daily[$key]['savings_withdrawals'] += $this->savingsWithdrawalAmount($transaction);
        }

        $rows = collect($daily)
            ->map(function (array $row): array {
                $totalHandled = $row['loan_collections'] + $row['savings_deposits'];
                $netCash = $totalHandled - $row['savings_withdrawals'];

                return [
                    'sort_at' => $row['date'],
                    'date' => $row['date']->format('M d, Y'),
                    'cashier' => $row['cashier'],
                    'transactions' => number_format((int) $row['transactions']),
                    'loan_collections' => $this->money($row['loan_collections']),
                    'savings_deposits' => $this->money($row['savings_deposits']),
                    'savings_withdrawals' => $this->money($row['savings_withdrawals']),
                    'total_handled' => $this->money($totalHandled),
                    'net_cash' => $this->money($netCash),
                ];
            })
            ->sortByDesc('sort_at')
            ->values()
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            });

        $cashierRows = $rows
            ->groupBy('cashier')
            ->map(function (Collection $group, string $cashier): array {
                $loanCollections = $group->sum(fn (array $row): float => $this->parseMoney($row['loan_collections']));
                $deposits = $group->sum(fn (array $row): float => $this->parseMoney($row['savings_deposits']));
                $withdrawals = $group->sum(fn (array $row): float => $this->parseMoney($row['savings_withdrawals']));

                return [
                    'cashier' => $cashier,
                    'days_reported' => number_format($group->count()),
                    'transactions' => number_format($group->sum(fn (array $row): int => (int) str_replace(',', '', $row['transactions']))),
                    'loan_collections' => $this->money($loanCollections),
                    'savings_deposits' => $this->money($deposits),
                    'savings_withdrawals' => $this->money($withdrawals),
                    'net_cash' => $this->money(($loanCollections + $deposits) - $withdrawals),
                ];
            })
            ->sortByDesc(fn (array $row): float => $this->parseMoney($row['net_cash']))
            ->values();

        $totalTransactions = $rows->sum(fn (array $row): int => (int) str_replace(',', '', $row['transactions']));
        $totalLoanCollections = $rows->sum(fn (array $row): float => $this->parseMoney($row['loan_collections']));
        $totalDeposits = $rows->sum(fn (array $row): float => $this->parseMoney($row['savings_deposits']));
        $totalWithdrawals = $rows->sum(fn (array $row): float => $this->parseMoney($row['savings_withdrawals']));
        $closingBalance = ($totalLoanCollections + $totalDeposits) - $totalWithdrawals;

        return $this->baseReportPayload(
            title: 'Cashier Summary Report',
            subtitle: 'Daily cashier handling summary across loan and savings posting activity.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Cashiers', 'value' => number_format($cashierRows->count())],
                ['label' => 'Transactions', 'value' => number_format($totalTransactions)],
                ['label' => 'Opening Balance', 'value' => $this->money(0)],
                ['label' => 'Closing Balance', 'value' => $this->money($closingBalance)],
                ['label' => 'Net Cash', 'value' => $this->money($closingBalance)],
            ],
            mainTable: [
                'title' => 'Daily Cashier Activity',
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'cashier', 'label' => 'Cashier'],
                    ['key' => 'transactions', 'label' => 'Transactions', 'align' => 'right'],
                    ['key' => 'loan_collections', 'label' => 'Loan Collections', 'align' => 'right'],
                    ['key' => 'savings_deposits', 'label' => 'Savings Deposits', 'align' => 'right'],
                    ['key' => 'savings_withdrawals', 'label' => 'Savings Withdrawals', 'align' => 'right'],
                    ['key' => 'total_handled', 'label' => 'Total Handled', 'align' => 'right'],
                    ['key' => 'net_cash', 'label' => 'Net Cash', 'align' => 'right'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'transactions' => number_format($totalTransactions),
                    'loan_collections' => $this->money($totalLoanCollections),
                    'savings_deposits' => $this->money($totalDeposits),
                    'savings_withdrawals' => $this->money($totalWithdrawals),
                    'total_handled' => $this->money($totalLoanCollections + $totalDeposits),
                    'net_cash' => $this->money($closingBalance),
                ],
            ],
            sections: [
                [
                    'title' => 'Cashier Totals',
                    'columns' => [
                        ['key' => 'cashier', 'label' => 'Cashier'],
                        ['key' => 'days_reported', 'label' => 'Days', 'align' => 'right'],
                        ['key' => 'transactions', 'label' => 'Transactions', 'align' => 'right'],
                        ['key' => 'loan_collections', 'label' => 'Loan Collections', 'align' => 'right'],
                        ['key' => 'savings_deposits', 'label' => 'Deposits', 'align' => 'right'],
                        ['key' => 'savings_withdrawals', 'label' => 'Withdrawals', 'align' => 'right'],
                        ['key' => 'net_cash', 'label' => 'Net Cash', 'align' => 'right'],
                    ],
                    'rows' => $cashierRows,
                ],
            ],
        );
    }

    protected function buildMemberAccountReport(array $filters, User $user): array
    {
        $members = $this->memberScopeQuery($filters)
            ->with(['profile', 'branch'])
            ->orderBy('id')
            ->get();

        $memberIds = $members->pluck('id')->filter()->unique()->values();
        $profileIds = $members->pluck('profile_id')->filter()->unique()->values();

        $loanAccounts = LoanAccount::query()
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
            ])
            ->when($memberIds->isNotEmpty(), function (Builder $query) use ($memberIds): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereIn('id', $memberIds->all()));
            })
            ->whereDate('release_date', '<=', $filters['end']->toDateString())
            ->get();

        $loanPayments = LoanPayment::query()
            ->where('status', 'Posted')
            ->when($memberIds->isNotEmpty(), function (Builder $query) use ($memberIds): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereIn('id', $memberIds->all()));
            })
            ->whereDate('payment_date', '<=', $filters['end']->toDateString())
            ->get();

        $savingsTransactions = SavingsAccountTransaction::query()
            ->when($profileIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('profile_id', $profileIds->all()))
            ->where(function (Builder $query) use ($filters): void {
                $query->whereDate('transaction_date', '<=', $filters['end']->toDateString())
                    ->orWhere(function (Builder $fallbackQuery) use ($filters): void {
                        $fallbackQuery->whereNull('transaction_date')
                            ->whereDate('created_at', '<=', $filters['end']->toDateString());
                    });
            })
            ->get();

        $loanAccountsByMember = $loanAccounts->groupBy(fn (LoanAccount $loanAccount): ?int => $loanAccount->loanApplication?->member_id);
        $loanPaymentsByMember = $loanPayments->groupBy(fn (LoanPayment $loanPayment): ?int => $loanPayment->loanApplication?->member_id);
        $savingsByProfile = $savingsTransactions->groupBy('profile_id');

        $rows = $members->map(function (MemberDetail $member) use ($loanAccountsByMember, $loanPaymentsByMember, $savingsByProfile): array {
            /** @var Collection<int, LoanAccount> $memberLoans */
            $memberLoans = $loanAccountsByMember->get($member->id, collect());
            /** @var Collection<int, LoanPayment> $memberPayments */
            $memberPayments = $loanPaymentsByMember->get($member->id, collect());
            /** @var Collection<int, SavingsAccountTransaction> $memberSavings */
            $memberSavings = $savingsByProfile->get($member->profile_id, collect());

            $savingsBalance = $memberSavings->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction) - $this->savingsWithdrawalAmount($transaction));
            $activeLoans = $memberLoans->whereIn('status', ['Active', 'Restructured'])->count();
            $outstandingBalance = $memberLoans->whereIn('status', ['Active', 'Restructured'])->sum('balance');
            $lastPaymentDate = $memberPayments->sortByDesc('payment_date')->first()?->payment_date;

            return [
                'member_no' => $member->member_no ?: '—',
                'member' => $member->profile?->full_name ?? 'Unknown Member',
                'branch' => $member->branch?->name ?? '—',
                'status' => $member->status ?? '—',
                'share_capital' => $this->money($member->share_capital_balance),
                'savings_balance' => $this->money($savingsBalance),
                'active_loans' => number_format($activeLoans),
                'outstanding_balance' => $this->money($outstandingBalance),
                'last_payment' => $lastPaymentDate?->format('M d, Y') ?? '—',
            ];
        })->values();

        $statusRows = $members
            ->groupBy(fn (MemberDetail $member): string => $member->status ?: 'Unspecified')
            ->map(function (Collection $group, string $status): array {
                return [
                    'status' => $status,
                    'members' => number_format($group->count()),
                ];
            })
            ->sortByDesc(fn (array $row): int => (int) str_replace(',', '', $row['members']))
            ->values();

        $totalSavings = $rows->sum(fn (array $row): float => $this->parseMoney($row['savings_balance']));
        $totalShareCapital = $rows->sum(fn (array $row): float => $this->parseMoney($row['share_capital']));
        $totalOutstanding = $rows->sum(fn (array $row): float => $this->parseMoney($row['outstanding_balance']));

        return $this->baseReportPayload(
            title: 'Member Account Report',
            subtitle: 'Member-level balances and account status for account officer monitoring.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Members', 'value' => number_format($rows->count())],
                ['label' => 'Savings Balance', 'value' => $this->money($totalSavings)],
                ['label' => 'Share Capital', 'value' => $this->money($totalShareCapital)],
                ['label' => 'Outstanding Loans', 'value' => $this->money($totalOutstanding)],
            ],
            mainTable: [
                'title' => 'Member Accounts',
                'columns' => [
                    ['key' => 'member_no', 'label' => 'Member No.'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'share_capital', 'label' => 'Share Capital', 'align' => 'right'],
                    ['key' => 'savings_balance', 'label' => 'Savings Balance', 'align' => 'right'],
                    ['key' => 'active_loans', 'label' => 'Active Loans', 'align' => 'right'],
                    ['key' => 'outstanding_balance', 'label' => 'Outstanding Loan', 'align' => 'right'],
                    ['key' => 'last_payment', 'label' => 'Last Payment'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'share_capital' => $this->money($totalShareCapital),
                    'savings_balance' => $this->money($totalSavings),
                    'active_loans' => number_format($rows->sum(fn (array $row): int => (int) str_replace(',', '', $row['active_loans']))),
                    'outstanding_balance' => $this->money($totalOutstanding),
                ],
            ],
            sections: [
                [
                    'title' => 'Membership Status Breakdown',
                    'columns' => [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'members', 'label' => 'Members', 'align' => 'right'],
                    ],
                    'rows' => $statusRows,
                ],
            ],
        );
    }

    protected function buildCollectionMonitoringReport(array $filters, User $user): array
    {
        $loanAccounts = $this->loanAccountQuery($filters)
            ->whereIn('status', ['Active', 'Restructured'])
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
                'profile.memberDetail.branch',
            ])
            ->orderBy('maturity_date')
            ->orderBy('loan_account_id')
            ->get();

        $rows = $loanAccounts->map(function (LoanAccount $loanAccount) use ($filters): array {
            $schedule = $this->loanScheduleService->build($loanAccount);
            $summary = $this->summarizeLoanSchedule($schedule);

            $nextDueDate = $summary['next_due_date'];
            $daysOverdue = $summary['days_overdue'];
            $dueAmount = $summary['next_unpaid_amount'] > 0
                ? $summary['next_unpaid_amount']
                : (float) $loanAccount->monthly_amortization;

            $status = 'Current';

            if ($daysOverdue > 0) {
                $status = 'Overdue';
            } elseif ($nextDueDate && $nextDueDate->lessThanOrEqualTo($filters['end']->copy()->addDays(7))) {
                $status = 'Due Soon';
            }

            return [
                'sort_date' => $nextDueDate ?? Carbon::parse($loanAccount->maturity_date ?? now()),
                'member' => $loanAccount->loanApplication?->member?->profile?->full_name
                    ?? $loanAccount->profile?->full_name
                    ?? 'Unknown Member',
                'branch' => $loanAccount->loanApplication?->member?->branch?->name
                    ?? $loanAccount->profile?->memberDetail?->branch?->name
                    ?? '—',
                'loan_reference' => 'Loan #'.$loanAccount->loan_account_id,
                'loan_type' => $loanAccount->loanApplication?->type?->name ?? '—',
                'next_due_date' => $nextDueDate?->format('M d, Y') ?? '—',
                'due_amount' => $this->money($dueAmount),
                'days_overdue' => number_format($daysOverdue),
                'balance' => $this->money($loanAccount->balance),
                'status' => $status,
            ];
        })
            ->sortBy('sort_date')
            ->values()
            ->map(function (array $row): array {
                unset($row['sort_date']);

                return $row;
            });

        $overdueAccounts = $rows->filter(fn (array $row): bool => $row['status'] === 'Overdue');
        $dueSoonAccounts = $rows->filter(fn (array $row): bool => $row['status'] === 'Due Soon');

        $bucketRows = [
            ['bucket' => 'Overdue', 'accounts' => number_format($overdueAccounts->count()), 'amount' => $this->money($overdueAccounts->sum(fn (array $row): float => $this->parseMoney($row['due_amount'])))],
            ['bucket' => 'Due Soon (7 Days)', 'accounts' => number_format($dueSoonAccounts->count()), 'amount' => $this->money($dueSoonAccounts->sum(fn (array $row): float => $this->parseMoney($row['due_amount'])))],
            ['bucket' => 'Current', 'accounts' => number_format($rows->where('status', 'Current')->count()), 'amount' => $this->money($rows->where('status', 'Current')->sum(fn (array $row): float => $this->parseMoney($row['due_amount'])))],
        ];

        return $this->baseReportPayload(
            title: 'Collection Monitoring Report',
            subtitle: 'Due amounts and overdue exposure to support proactive collection follow-up.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Accounts Monitored', 'value' => number_format($rows->count())],
                ['label' => 'Overdue Accounts', 'value' => number_format($overdueAccounts->count())],
                ['label' => 'Due Soon', 'value' => number_format($dueSoonAccounts->count())],
                ['label' => 'Total Due Amount', 'value' => $this->money($rows->sum(fn (array $row): float => $this->parseMoney($row['due_amount'])))],
            ],
            mainTable: [
                'title' => 'Collection Monitoring Details',
                'columns' => [
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'next_due_date', 'label' => 'Next Due Date'],
                    ['key' => 'due_amount', 'label' => 'Due Amount', 'align' => 'right'],
                    ['key' => 'days_overdue', 'label' => 'Days Overdue', 'align' => 'right'],
                    ['key' => 'balance', 'label' => 'Outstanding', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'due_amount' => $this->money($rows->sum(fn (array $row): float => $this->parseMoney($row['due_amount']))),
                    'balance' => $this->money($rows->sum(fn (array $row): float => $this->parseMoney($row['balance']))),
                ],
            ],
            sections: [
                [
                    'title' => 'Due Bucket Summary',
                    'columns' => [
                        ['key' => 'bucket', 'label' => 'Bucket'],
                        ['key' => 'accounts', 'label' => 'Accounts', 'align' => 'right'],
                        ['key' => 'amount', 'label' => 'Amount', 'align' => 'right'],
                    ],
                    'rows' => $bucketRows,
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

    protected function buildDelinquentAccountsReport(array $filters, User $user): array
    {
        $report = $this->buildDelinquencyReport($filters, $user);
        $report['title'] = 'Delinquent Accounts Report';
        $report['subtitle'] = 'Overdue accounts and unpaid dues for account officer collection management.';

        return $report;
    }

    protected function buildLoanApplicationReport(array $filters, User $user): array
    {
        $applications = $this->loanApplicationQuery($filters, true)
            ->with([
                'member.profile',
                'type',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('loan_application_id')
            ->get();

        $rows = $applications->map(function (LoanApplication $application): array {
            $submittedAt = $this->loanApplicationDate($application);

            return [
                'application_no' => 'App #'.$application->loan_application_id,
                'member' => $application->member?->profile?->full_name ?? 'Unknown Member',
                'branch' => $application->member?->branch?->name ?? '—',
                'loan_type' => $application->type?->name ?? '—',
                'application_type' => $application->application_type ?? 'New',
                'amount_requested' => $this->money($application->amount_requested),
                'term_months' => number_format((int) $application->term_months),
                'status' => $application->status,
                'submitted_at' => $submittedAt->format('M d, Y'),
                'stage' => $this->loanApprovalStage($application),
                'purpose' => $this->excerpt($application->purpose, 60),
            ];
        })->values();

        $statusRows = $applications
            ->groupBy(fn (LoanApplication $application): string => $application->status)
            ->map(function (Collection $group, string $status): array {
                return [
                    'status' => $status,
                    'applications' => number_format($group->count()),
                    'amount_requested' => $this->money($group->sum('amount_requested')),
                ];
            })
            ->sortByDesc(fn (array $row): int => (int) str_replace(',', '', $row['applications']))
            ->values();

        return $this->baseReportPayload(
            title: 'Loan Application Report',
            subtitle: 'Pipeline of submitted loan applications by status and approval stage.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Applications', 'value' => number_format($applications->count())],
                ['label' => 'Requested Amount', 'value' => $this->money($applications->sum('amount_requested'))],
                ['label' => 'Pending', 'value' => number_format($applications->where('status', 'Pending')->count())],
                ['label' => 'Under Review', 'value' => number_format($applications->where('status', 'Under Review')->count())],
                ['label' => 'Approved', 'value' => number_format($applications->where('status', 'Approved')->count())],
            ],
            mainTable: [
                'title' => 'Applications',
                'columns' => [
                    ['key' => 'application_no', 'label' => 'Application'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'application_type', 'label' => 'Type'],
                    ['key' => 'amount_requested', 'label' => 'Requested', 'align' => 'right'],
                    ['key' => 'term_months', 'label' => 'Term (Months)', 'align' => 'right'],
                    ['key' => 'submitted_at', 'label' => 'Submitted'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'stage', 'label' => 'Stage'],
                    ['key' => 'purpose', 'label' => 'Purpose'],
                ],
                'rows' => $rows,
            ],
            sections: [
                [
                    'title' => 'Status Breakdown',
                    'columns' => [
                        ['key' => 'status', 'label' => 'Status'],
                        ['key' => 'applications', 'label' => 'Applications', 'align' => 'right'],
                        ['key' => 'amount_requested', 'label' => 'Requested', 'align' => 'right'],
                    ],
                    'rows' => $statusRows,
                ],
            ],
        );
    }

    protected function buildLoanEvaluationReport(array $filters, User $user): array
    {
        $applications = $this->loanApplicationQuery($filters, true)
            ->with([
                'member.profile',
                'type',
                'cashflows',
                'loanAccount',
            ])
            ->orderByDesc('submitted_at')
            ->orderByDesc('loan_application_id')
            ->get();

        $rows = $applications->map(function (LoanApplication $application): array {
            $income = $application->cashflows
                ->filter(fn ($cashflow): bool => $cashflow->row_type === 'income')
                ->sum('amount');

            $expenses = $application->cashflows
                ->filter(fn ($cashflow): bool => in_array($cashflow->row_type, ['expense', 'debt'], true))
                ->sum('amount');

            $netIncome = (float) $income - (float) $expenses;

            $estimatedAmortization = $application->loanAccount?->monthly_amortization
                ? (float) $application->loanAccount->monthly_amortization
                : $this->estimateMonthlyAmortization(
                    (float) $application->amount_requested,
                    (int) max((int) $application->term_months, 1),
                    (float) ($application->type?->max_interest_rate ?? 0),
                );

            $capacityRatio = $estimatedAmortization > 0
                ? ($netIncome / $estimatedAmortization)
                : 0;

            $recommendation = $capacityRatio >= 1.20
                ? 'Recommended'
                : ($capacityRatio >= 1.00 ? 'Borderline' : 'Needs Review');

            return [
                'application_no' => 'App #'.$application->loan_application_id,
                'member' => $application->member?->profile?->full_name ?? 'Unknown Member',
                'loan_type' => $application->type?->name ?? '—',
                'amount_requested' => $this->money($application->amount_requested),
                'monthly_income' => $this->money($income),
                'monthly_expenses' => $this->money($expenses),
                'net_income' => $this->money($netIncome),
                'estimated_amortization' => $this->money($estimatedAmortization),
                'capacity_ratio' => number_format($capacityRatio, 2).'x',
                'recommendation' => $recommendation,
                'evaluation_notes' => $this->excerpt($application->evaluation_notes, 70),
            ];
        })->values();

        $recommended = $rows->where('recommendation', 'Recommended')->count();
        $borderline = $rows->where('recommendation', 'Borderline')->count();
        $needsReview = $rows->where('recommendation', 'Needs Review')->count();

        return $this->baseReportPayload(
            title: 'Loan Evaluation Report',
            subtitle: 'Cashflow-based repayment capacity checks and evaluator recommendations.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Evaluated Applications', 'value' => number_format($rows->count())],
                ['label' => 'Recommended', 'value' => number_format($recommended)],
                ['label' => 'Borderline', 'value' => number_format($borderline)],
                ['label' => 'Needs Review', 'value' => number_format($needsReview)],
            ],
            mainTable: [
                'title' => 'Evaluation Metrics',
                'columns' => [
                    ['key' => 'application_no', 'label' => 'Application'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'amount_requested', 'label' => 'Requested', 'align' => 'right'],
                    ['key' => 'monthly_income', 'label' => 'Income', 'align' => 'right'],
                    ['key' => 'monthly_expenses', 'label' => 'Expenses', 'align' => 'right'],
                    ['key' => 'net_income', 'label' => 'Net Income', 'align' => 'right'],
                    ['key' => 'estimated_amortization', 'label' => 'Est. Amortization', 'align' => 'right'],
                    ['key' => 'capacity_ratio', 'label' => 'Capacity', 'align' => 'right'],
                    ['key' => 'recommendation', 'label' => 'Recommendation'],
                    ['key' => 'evaluation_notes', 'label' => 'Notes'],
                ],
                'rows' => $rows,
            ],
        );
    }

    protected function buildApprovedLoansReport(array $filters, User $user): array
    {
        $loanAccounts = $this->loanAccountQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
            ])
            ->whereBetween('release_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
            ->orderByDesc('release_date')
            ->orderByDesc('loan_account_id')
            ->get();

        $approverIds = $loanAccounts
            ->pluck('loanApplication.manager_approved_by_user_id')
            ->merge($loanAccounts->pluck('loanApplication.admin_approved_by_user_id'))
            ->filter()
            ->unique()
            ->values();

        $approvers = $this->mapActorNames($approverIds);

        $rows = $loanAccounts->map(function (LoanAccount $loanAccount) use ($approvers): array {
            $application = $loanAccount->loanApplication;

            return [
                'loan_reference' => 'Loan #'.$loanAccount->loan_account_id,
                'application_no' => $application?->loan_application_id ? 'App #'.$application->loan_application_id : '—',
                'member' => $application?->member?->profile?->full_name
                    ?? $loanAccount->profile?->full_name
                    ?? 'Unknown Member',
                'loan_type' => $application?->type?->name ?? '—',
                'release_date' => $loanAccount->release_date?->format('M d, Y') ?? '—',
                'principal_amount' => $this->money($loanAccount->principal_amount),
                'interest_rate' => number_format((float) $loanAccount->interest_rate, 2).'%',
                'term_months' => number_format((int) $loanAccount->term_months),
                'monthly_amortization' => $this->money($loanAccount->monthly_amortization),
                'manager_approved_by' => $application?->manager_approved_by_user_id
                    ? $this->actorName((int) $application->manager_approved_by_user_id, $approvers, 'Manager')
                    : '—',
                'admin_approved_by' => $application?->admin_approved_by_user_id
                    ? $this->actorName((int) $application->admin_approved_by_user_id, $approvers, 'Admin')
                    : '—',
            ];
        })->values();

        $loanTypeRows = $loanAccounts
            ->groupBy(fn (LoanAccount $loanAccount): string => $loanAccount->loanApplication?->type?->name ?? 'Uncategorized')
            ->map(function (Collection $group, string $loanType): array {
                return [
                    'loan_type' => $loanType,
                    'accounts' => number_format($group->count()),
                    'principal_amount' => $this->money($group->sum('principal_amount')),
                ];
            })
            ->sortByDesc(fn (array $row): float => $this->parseMoney($row['principal_amount']))
            ->values();

        return $this->baseReportPayload(
            title: 'Approved Loans Report',
            subtitle: 'Released loan accounts and approval trail for the selected period.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Approved Loans', 'value' => number_format($loanAccounts->count())],
                ['label' => 'Total Principal', 'value' => $this->money($loanAccounts->sum('principal_amount'))],
                ['label' => 'Average Principal', 'value' => $this->money($loanAccounts->count() > 0 ? $loanAccounts->avg('principal_amount') : 0)],
            ],
            mainTable: [
                'title' => 'Approved and Released Loans',
                'columns' => [
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'application_no', 'label' => 'Application'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'release_date', 'label' => 'Release Date'],
                    ['key' => 'principal_amount', 'label' => 'Principal', 'align' => 'right'],
                    ['key' => 'interest_rate', 'label' => 'Rate', 'align' => 'right'],
                    ['key' => 'term_months', 'label' => 'Term', 'align' => 'right'],
                    ['key' => 'monthly_amortization', 'label' => 'Amortization', 'align' => 'right'],
                    ['key' => 'manager_approved_by', 'label' => 'Manager Approved By'],
                    ['key' => 'admin_approved_by', 'label' => 'Admin Approved By'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'principal_amount' => $this->money($loanAccounts->sum('principal_amount')),
                    'monthly_amortization' => $this->money($loanAccounts->sum('monthly_amortization')),
                ],
            ],
            sections: [
                [
                    'title' => 'Approved Loans by Type',
                    'columns' => [
                        ['key' => 'loan_type', 'label' => 'Loan Type'],
                        ['key' => 'accounts', 'label' => 'Accounts', 'align' => 'right'],
                        ['key' => 'principal_amount', 'label' => 'Principal', 'align' => 'right'],
                    ],
                    'rows' => $loanTypeRows,
                ],
            ],
        );
    }

    protected function buildRestructuredLoansReport(array $filters, User $user): array
    {
        $restructureApplications = RestructureApplication::query()
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
                'oldLoanAccount',
            ])
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereBetween('created_at', [$filters['start'], $filters['end']])
            ->orderByDesc('created_at')
            ->orderByDesc('restructure_application_id')
            ->get();

        $statusLogs = RestructureApplicationStatusLog::query()
            ->with('restructureApplication.loanApplication.member.profile')
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('restructureApplication.loanApplication.member', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('restructureApplication.loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->whereBetween('changed_at', [$filters['start'], $filters['end']])
            ->orderByDesc('changed_at')
            ->limit(300)
            ->get();

        $actorNames = $this->mapActorNames($statusLogs->pluck('changed_by_user_id')->filter()->unique()->values());

        $rows = $restructureApplications->map(function (RestructureApplication $application): array {
            return [
                'restructure_no' => 'Restructure #'.$application->restructure_application_id,
                'member' => $application->loanApplication?->member?->profile?->full_name ?? 'Unknown Member',
                'branch' => $application->loanApplication?->member?->branch?->name ?? '—',
                'loan_type' => $application->loanApplication?->type?->name ?? '—',
                'old_loan' => $application->old_loan_account_id ? 'Loan #'.$application->old_loan_account_id : '—',
                'new_principal' => $this->money($application->new_principal),
                'new_interest' => number_format((float) $application->new_interest, 2).'%',
                'term_months' => number_format((int) ($application->term_months ?? 0)),
                'new_maturity_date' => $application->new_maturity_date ? Carbon::parse($application->new_maturity_date)->format('M d, Y') : '—',
                'status' => Str::title((string) $application->status),
                'requested_at' => $application->created_at?->format('M d, Y') ?? '—',
                'remarks' => $this->excerpt($application->remarks, 60),
            ];
        })->values();

        $statusLogRows = $statusLogs->map(function (RestructureApplicationStatusLog $log) use ($actorNames): array {
            return [
                'changed_at' => Carbon::parse($log->changed_at)->format('M d, Y h:i A'),
                'restructure_no' => 'Restructure #'.$log->restructure_application_id,
                'member' => $log->restructureApplication?->loanApplication?->member?->profile?->full_name ?? 'Unknown Member',
                'transition' => ($log->from_status ?? '—').' -> '.($log->to_status ?? '—'),
                'changed_by' => $this->actorName($log->changed_by_user_id ? (int) $log->changed_by_user_id : null, $actorNames),
                'reason' => $log->reason ?: '—',
            ];
        })->values();

        return $this->baseReportPayload(
            title: 'Restructured Loans Report',
            subtitle: 'Restructure applications and decision history for rescheduled or modified loans.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Restructure Applications', 'value' => number_format($restructureApplications->count())],
                ['label' => 'Approved', 'value' => number_format($restructureApplications->where('status', 'approved')->count())],
                ['label' => 'Pending', 'value' => number_format($restructureApplications->where('status', 'pending')->count())],
                ['label' => 'New Principal Total', 'value' => $this->money($restructureApplications->sum('new_principal'))],
            ],
            mainTable: [
                'title' => 'Restructure Applications',
                'columns' => [
                    ['key' => 'restructure_no', 'label' => 'Reference'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'old_loan', 'label' => 'Old Loan'],
                    ['key' => 'new_principal', 'label' => 'New Principal', 'align' => 'right'],
                    ['key' => 'new_interest', 'label' => 'New Rate', 'align' => 'right'],
                    ['key' => 'term_months', 'label' => 'Term', 'align' => 'right'],
                    ['key' => 'new_maturity_date', 'label' => 'Maturity'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'requested_at', 'label' => 'Requested'],
                    ['key' => 'remarks', 'label' => 'Remarks'],
                ],
                'rows' => $rows,
            ],
            sections: [
                [
                    'title' => 'Restructure Status Logs',
                    'columns' => [
                        ['key' => 'changed_at', 'label' => 'Changed At'],
                        ['key' => 'restructure_no', 'label' => 'Reference'],
                        ['key' => 'member', 'label' => 'Member'],
                        ['key' => 'transition', 'label' => 'Transition'],
                        ['key' => 'changed_by', 'label' => 'Changed By'],
                        ['key' => 'reason', 'label' => 'Reason'],
                    ],
                    'rows' => $statusLogRows,
                ],
            ],
        );
    }

    protected function buildLoanStatementReport(array $filters, User $user): array
    {
        $loanAccounts = $this->loanAccountQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanApplication.type',
                'profile.memberDetail.branch',
            ])
            ->orderByDesc('release_date')
            ->orderByDesc('loan_account_id')
            ->get();

        $loanPayments = $this->dailyLoanPaymentQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanAccount.loanApplication.type',
                'postedBy.profile',
            ])
            ->orderByDesc('payment_date')
            ->orderByDesc('loan_payment_id')
            ->get();

        $loanRows = $loanAccounts->map(function (LoanAccount $loanAccount): array {
            $summary = $this->summarizeLoanSchedule($this->loanScheduleService->build($loanAccount));

            return [
                'member' => $loanAccount->loanApplication?->member?->profile?->full_name
                    ?? $loanAccount->profile?->full_name
                    ?? 'Unknown Member',
                'loan_reference' => 'Loan #'.$loanAccount->loan_account_id,
                'loan_type' => $loanAccount->loanApplication?->type?->name ?? '—',
                'release_date' => $loanAccount->release_date?->format('M d, Y') ?? '—',
                'maturity_date' => $loanAccount->maturity_date?->format('M d, Y') ?? '—',
                'status' => $loanAccount->status ?? '—',
                'principal_amount' => $this->money($loanAccount->principal_amount),
                'balance' => $this->money($loanAccount->balance),
                'next_due_date' => $summary['next_due_date']?->format('M d, Y') ?? '—',
                'days_overdue' => number_format($summary['days_overdue']),
            ];
        })->values();

        $paymentRows = $loanPayments->map(function (LoanPayment $payment): array {
            return [
                'payment_date' => $payment->payment_date?->format('M d, Y') ?? '—',
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

        return $this->baseReportPayload(
            title: 'Loan Statement Report',
            subtitle: 'Loan account balances with payment activity for selected member scope.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Loan Accounts', 'value' => number_format($loanAccounts->count())],
                ['label' => 'Outstanding Balance', 'value' => $this->money($loanAccounts->sum('balance'))],
                ['label' => 'Payments in Period', 'value' => $this->money($loanPayments->sum('amount_paid'))],
                ['label' => 'Interest in Period', 'value' => $this->money($loanPayments->sum('interest_paid'))],
            ],
            mainTable: [
                'title' => 'Loan Accounts',
                'columns' => [
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'loan_type', 'label' => 'Loan Type'],
                    ['key' => 'release_date', 'label' => 'Release Date'],
                    ['key' => 'maturity_date', 'label' => 'Maturity Date'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'principal_amount', 'label' => 'Principal', 'align' => 'right'],
                    ['key' => 'balance', 'label' => 'Balance', 'align' => 'right'],
                    ['key' => 'next_due_date', 'label' => 'Next Due Date'],
                    ['key' => 'days_overdue', 'label' => 'Days Overdue', 'align' => 'right'],
                ],
                'rows' => $loanRows,
                'totals_label' => 'Totals',
                'totals' => [
                    'principal_amount' => $this->money($loanAccounts->sum('principal_amount')),
                    'balance' => $this->money($loanAccounts->sum('balance')),
                ],
            ],
            sections: [
                [
                    'title' => 'Payment Activity',
                    'columns' => [
                        ['key' => 'payment_date', 'label' => 'Date'],
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

    protected function buildPaymentHistoryReport(array $filters, User $user): array
    {
        $loanPayments = $this->dailyLoanPaymentQuery($filters)
            ->with([
                'loanApplication.member.profile',
                'loanAccount.loanApplication.type',
                'postedBy.profile',
            ])
            ->orderByDesc('payment_date')
            ->orderByDesc('loan_payment_id')
            ->get();

        $rows = $loanPayments->map(function (LoanPayment $payment): array {
            return [
                'payment_date' => $payment->payment_date?->format('M d, Y') ?? '—',
                'member' => $payment->loanApplication?->member?->profile?->full_name
                    ?? $payment->loanAccount?->profile?->full_name
                    ?? 'Unknown Member',
                'loan_reference' => $payment->loanAccount?->loan_account_id
                    ? 'Loan #'.$payment->loanAccount->loan_account_id
                    : 'Loan App #'.$payment->loan_application_id,
                'payment_type' => $payment->payment_type ?? '—',
                'amount_paid' => $this->money($payment->amount_paid),
                'principal_paid' => $this->money($payment->principal_paid),
                'interest_paid' => $this->money($payment->interest_paid),
                'penalty_paid' => $this->money($payment->penalty_paid),
                'remaining_balance' => $this->money($payment->remaining_balance),
                'status' => $payment->status ?? '—',
                'posted_by' => $payment->postedBy?->name ?? 'System',
            ];
        })->values();

        return $this->baseReportPayload(
            title: 'Payment History Report',
            subtitle: 'Chronological record of posted loan payments.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Payments', 'value' => number_format($loanPayments->count())],
                ['label' => 'Amount Paid', 'value' => $this->money($loanPayments->sum('amount_paid'))],
                ['label' => 'Principal Paid', 'value' => $this->money($loanPayments->sum('principal_paid'))],
                ['label' => 'Interest Paid', 'value' => $this->money($loanPayments->sum('interest_paid'))],
                ['label' => 'Penalties Paid', 'value' => $this->money($loanPayments->sum('penalty_paid'))],
            ],
            mainTable: [
                'title' => 'Payment History',
                'columns' => [
                    ['key' => 'payment_date', 'label' => 'Date'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'loan_reference', 'label' => 'Loan'],
                    ['key' => 'payment_type', 'label' => 'Payment Type'],
                    ['key' => 'amount_paid', 'label' => 'Amount Paid', 'align' => 'right'],
                    ['key' => 'principal_paid', 'label' => 'Principal', 'align' => 'right'],
                    ['key' => 'interest_paid', 'label' => 'Interest', 'align' => 'right'],
                    ['key' => 'penalty_paid', 'label' => 'Penalty', 'align' => 'right'],
                    ['key' => 'remaining_balance', 'label' => 'Remaining', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'posted_by', 'label' => 'Posted By'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'amount_paid' => $this->money($loanPayments->sum('amount_paid')),
                    'principal_paid' => $this->money($loanPayments->sum('principal_paid')),
                    'interest_paid' => $this->money($loanPayments->sum('interest_paid')),
                    'penalty_paid' => $this->money($loanPayments->sum('penalty_paid')),
                ],
            ],
        );
    }

    protected function buildSavingsStatementReport(array $filters, User $user): array
    {
        $savingsTransactions = $this->savingsTransactionQuery($filters, true)
            ->with([
                'member.memberDetail.branch',
                'postedBy.profile',
            ])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        $rows = $savingsTransactions->map(function (SavingsAccountTransaction $transaction): array {
            $date = $this->savingsTransactionDate($transaction);
            $deposit = $this->savingsDepositAmount($transaction);
            $withdrawal = $this->savingsWithdrawalAmount($transaction);

            return [
                'date' => $date->format('M d, Y'),
                'member' => $transaction->member?->full_name ?? 'Unknown Member',
                'branch' => $transaction->member?->memberDetail?->branch?->name ?? '—',
                'type' => $transaction->type ?? $transaction->direction ?? '—',
                'reference' => $transaction->reference_no ?? '—',
                'deposit' => $this->money($deposit),
                'withdrawal' => $this->money($withdrawal),
                'net_movement' => $this->money($deposit - $withdrawal),
                'status' => $transaction->status ?? '—',
                'posted_by' => $transaction->postedBy?->name ?? 'System',
            ];
        })->values();

        $memberRows = $rows
            ->groupBy('member')
            ->map(function (Collection $group, string $member): array {
                $deposits = $group->sum(fn (array $row): float => $this->parseMoney($row['deposit']));
                $withdrawals = $group->sum(fn (array $row): float => $this->parseMoney($row['withdrawal']));

                return [
                    'member' => $member,
                    'transactions' => number_format($group->count()),
                    'deposits' => $this->money($deposits),
                    'withdrawals' => $this->money($withdrawals),
                    'net_movement' => $this->money($deposits - $withdrawals),
                ];
            })
            ->sortByDesc(fn (array $row): float => $this->parseMoney($row['net_movement']))
            ->values();

        $totalDeposits = $rows->sum(fn (array $row): float => $this->parseMoney($row['deposit']));
        $totalWithdrawals = $rows->sum(fn (array $row): float => $this->parseMoney($row['withdrawal']));

        return $this->baseReportPayload(
            title: 'Savings Statement Report',
            subtitle: 'Savings deposits and withdrawals for selected members in the reporting period.',
            filters: $filters,
            user: $user,
            orientation: 'landscape',
            summaryCards: [
                ['label' => 'Transactions', 'value' => number_format($rows->count())],
                ['label' => 'Deposits', 'value' => $this->money($totalDeposits)],
                ['label' => 'Withdrawals', 'value' => $this->money($totalWithdrawals)],
                ['label' => 'Net Movement', 'value' => $this->money($totalDeposits - $totalWithdrawals)],
            ],
            mainTable: [
                'title' => 'Savings Transactions',
                'columns' => [
                    ['key' => 'date', 'label' => 'Date'],
                    ['key' => 'member', 'label' => 'Member'],
                    ['key' => 'branch', 'label' => 'Branch'],
                    ['key' => 'type', 'label' => 'Type'],
                    ['key' => 'reference', 'label' => 'Reference'],
                    ['key' => 'deposit', 'label' => 'Deposit', 'align' => 'right'],
                    ['key' => 'withdrawal', 'label' => 'Withdrawal', 'align' => 'right'],
                    ['key' => 'net_movement', 'label' => 'Net', 'align' => 'right'],
                    ['key' => 'status', 'label' => 'Status'],
                    ['key' => 'posted_by', 'label' => 'Posted By'],
                ],
                'rows' => $rows,
                'totals_label' => 'Totals',
                'totals' => [
                    'deposit' => $this->money($totalDeposits),
                    'withdrawal' => $this->money($totalWithdrawals),
                    'net_movement' => $this->money($totalDeposits - $totalWithdrawals),
                ],
            ],
            sections: [
                [
                    'title' => 'Savings by Member',
                    'columns' => [
                        ['key' => 'member', 'label' => 'Member'],
                        ['key' => 'transactions', 'label' => 'Transactions', 'align' => 'right'],
                        ['key' => 'deposits', 'label' => 'Deposits', 'align' => 'right'],
                        ['key' => 'withdrawals', 'label' => 'Withdrawals', 'align' => 'right'],
                        ['key' => 'net_movement', 'label' => 'Net', 'align' => 'right'],
                    ],
                    'rows' => $memberRows,
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

        $savingsTransactions = $this->savingsTransactionQuery($filters, true)
            ->with(['member.memberDetail.branch', 'postedBy.profile'])
            ->when($profileIds->isNotEmpty(), fn (Builder $query) => $query->whereIn('profile_id', $profileIds->all()))
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
                'date' => $this->savingsTransactionDate($transaction)->format('M d, Y'),
                'member' => $transaction->member?->full_name ?? 'Unknown Member',
                'type' => $transaction->type ?? $transaction->direction ?? '—',
                'amount' => $this->money($this->savingsDepositAmount($transaction) - $this->savingsWithdrawalAmount($transaction)),
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

        $savingsDeposits = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsDepositAmount($transaction));
        $savingsWithdrawals = $savingsTransactions->sum(fn (SavingsAccountTransaction $transaction): float => $this->savingsWithdrawalAmount($transaction));
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

    protected function loanApplicationQuery(array $filters, bool $withinPeriod = false): Builder
    {
        return LoanApplication::query()
            ->when($filters['member_id'], fn (Builder $query, int $memberId) => $query->where('member_id', $memberId))
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->when($withinPeriod, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $dateQuery) use ($filters): void {
                    $dateQuery->whereBetween('submitted_at', [$filters['start'], $filters['end']])
                        ->orWhere(function (Builder $fallbackQuery) use ($filters): void {
                            $fallbackQuery->whereNull('submitted_at')
                                ->whereBetween('created_at', [$filters['start'], $filters['end']]);
                        });
                });
            })
            ->whereDate('created_at', '<=', $filters['end']->toDateString());
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

    protected function savingsTransactionQuery(array $filters, bool $withinPeriod = true): Builder
    {
        return SavingsAccountTransaction::query()
            ->when($filters['member_id'], function (Builder $query, int $memberId): void {
                $query->whereHas('member.memberDetail', fn (Builder $memberQuery) => $memberQuery->whereKey($memberId));
            })
            ->when($filters['branch_id'], function (Builder $query, int $branchId): void {
                $query->whereHas('member.memberDetail', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId));
            })
            ->when($withinPeriod, function (Builder $query) use ($filters): void {
                $query->where(function (Builder $dateQuery) use ($filters): void {
                    $dateQuery->whereBetween('transaction_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
                        ->orWhere(function (Builder $fallbackQuery) use ($filters): void {
                            $fallbackQuery->whereNull('transaction_date')
                                ->whereBetween('created_at', [$filters['start'], $filters['end']]);
                        });
                });
            });
    }

    protected function shareCapitalTransactionQuery(array $filters, bool $withinPeriod = true): QueryBuilder
    {
        $query = DB::table('share_capital_transactions');

        if ($filters['member_id']) {
            $profileId = MemberDetail::query()->whereKey($filters['member_id'])->value('profile_id');

            if ($profileId) {
                $query->where('profile_id', $profileId);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($filters['branch_id']) {
            $profileIds = MemberDetail::query()
                ->where('branch_id', $filters['branch_id'])
                ->pluck('profile_id')
                ->filter()
                ->unique()
                ->values();

            if ($profileIds->isEmpty()) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('profile_id', $profileIds->all());
            }
        }

        if ($withinPeriod) {
            $query->where(function (QueryBuilder $dateQuery) use ($filters): void {
                $dateQuery->whereBetween('transaction_date', [$filters['start']->toDateString(), $filters['end']->toDateString()])
                    ->orWhere(function (QueryBuilder $fallbackQuery) use ($filters): void {
                        $fallbackQuery->whereNull('transaction_date')
                            ->whereBetween('created_at', [$filters['start'], $filters['end']]);
                    });
            });
        }

        return $query;
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
        $nextUnpaidAmount = (float) ($nextDueRow['unpaid_amount'] ?? 0);

        return [
            'remaining_principal' => round($remainingPrincipal, 2),
            'remaining_interest' => round($remainingInterest, 2),
            'remaining_penalties' => round($remainingPenalties, 2),
            'next_due_date' => isset($nextDueRow['due_date']) ? Carbon::parse($nextDueRow['due_date']) : null,
            'next_unpaid_amount' => round($nextUnpaidAmount, 2),
            'months_left' => $monthsLeft,
            'days_overdue' => $daysOverdue,
        ];
    }

    protected function scopedProfileIds(array $filters): Collection
    {
        return $this->memberScopeQuery($filters)
            ->pluck('profile_id')
            ->filter()
            ->unique()
            ->values();
    }

    protected function loanApplicationDate(LoanApplication $application): Carbon
    {
        return $application->submitted_at
            ? Carbon::parse($application->submitted_at)
            : Carbon::parse($application->created_at);
    }

    protected function savingsTransactionDate(SavingsAccountTransaction $transaction): Carbon
    {
        if ($transaction->transaction_date) {
            return Carbon::parse($transaction->transaction_date);
        }

        return Carbon::parse($transaction->created_at);
    }

    protected function savingsDepositAmount(SavingsAccountTransaction $transaction): float
    {
        $deposit = (float) ($transaction->deposit ?? 0);

        if ($deposit > 0) {
            return $deposit;
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['deposit', 'credit'], true) || in_array($direction, ['credit', 'inflow'], true)) {
            return (float) ($transaction->amount ?? 0);
        }

        return 0.0;
    }

    protected function savingsWithdrawalAmount(SavingsAccountTransaction $transaction): float
    {
        $withdrawal = (float) ($transaction->withdrawal ?? 0);

        if ($withdrawal > 0) {
            return $withdrawal;
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['withdrawal', 'debit'], true) || in_array($direction, ['debit', 'outflow'], true)) {
            return (float) ($transaction->amount ?? 0);
        }

        return 0.0;
    }

    protected function estimateMonthlyAmortization(float $principal, int $termMonths, float $annualRate): float
    {
        if ($termMonths <= 0) {
            return round($principal, 2);
        }

        $monthlyInterest = $annualRate > 0
            ? ($principal * ($annualRate / 100)) / 12
            : 0;

        return round(($principal / $termMonths) + $monthlyInterest, 2);
    }

    protected function monthBuckets(array $filters): Collection
    {
        $startMonth = $filters['start']->copy()->startOfMonth();
        $endMonth = $filters['end']->copy()->startOfMonth();

        return collect(CarbonPeriod::create($startMonth, '1 month', $endMonth))
            ->map(fn (Carbon $month): array => [
                'key' => $month->format('Y-m'),
                'label' => $month->format('M Y'),
            ]);
    }

    protected function mapActorNames(Collection $userIds): array
    {
        if ($userIds->isEmpty()) {
            return [];
        }

        return User::query()
            ->with('profile')
            ->whereIn('user_id', $userIds->all())
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->user_id => $user->name])
            ->all();
    }

    protected function actorName(?int $userId, array $actors, string $fallback = 'System'): string
    {
        if (! $userId) {
            return $fallback;
        }

        return $actors[$userId] ?? $fallback;
    }

    protected function loanApprovalStage(LoanApplication $application): string
    {
        if ($application->admin_approved_at) {
            return 'Final Approved';
        }

        if ($application->manager_approved_at && ! $application->admin_approved_at) {
            return 'For Admin Approval';
        }

        if ($application->status === 'Under Review') {
            return 'Under Review';
        }

        if ($application->status === 'Pending') {
            return 'Pending Evaluation';
        }

        return $application->status;
    }

    protected function excerpt(?string $text, int $limit = 80): string
    {
        if (! $text) {
            return '—';
        }

        return Str::limit(trim($text), $limit);
    }

    protected function formatPercent(float $value): string
    {
        return number_format($value, 2).'%';
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
