<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\MemberDetail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        [$start, $end] = $this->resolvePeriod($request);

        $snapshot = $this->snapshotMetrics();
        $periodMetrics = $this->periodMetrics($start, $end);

        return response()->json([
            'about' => [
                'title' => 'Reports & Summary',
                'description' => 'A live snapshot of member activity, loan movement, and monthly collections.',
                'period' => [
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ],
                'generated_at' => now()->toIso8601String(),
                'highlights' => [
                    'Active members: '.number_format($snapshot['active_members']),
                    'Active loans: '.number_format($snapshot['active_loans']),
                    'Delinquent members: '.number_format($snapshot['delinquent_members']),
                    'Collections in period: '.$this->currency($periodMetrics['totals']['collections']),
                ],
            ],
            'summary_cards' => $this->summaryCards($snapshot),
            'summary' => [
                'active_members' => $snapshot['active_members'],
                'active_loans' => $snapshot['active_loans'],
                'delinquent_members' => $snapshot['delinquent_members'],
                'total_loan_released' => round($periodMetrics['totals']['loan_releases'], 2),
                'total_collected' => round($periodMetrics['totals']['collections'], 2),
                'remaining_balance' => round($snapshot['remaining_balance'], 2),
            ],
            'chart' => $periodMetrics['chart'],
            'totals' => $periodMetrics['totals'],
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolvePeriod(Request $request): array
    {
        $defaultStart = now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString();
        $defaultEnd = now()->toDateString();

        $startInput = $request->filled('start_date')
            ? $request->input('start_date')
            : ($request->filled('startDate') ? $request->input('startDate') : $defaultStart);

        $endInput = $request->filled('end_date')
            ? $request->input('end_date')
            : ($request->filled('endDate') ? $request->input('endDate') : $defaultEnd);

        $start = Carbon::parse($startInput)->startOfDay();
        $end = Carbon::parse($endInput)->endOfDay();

        if ($end->lessThan($start)) {
            $end = $start->copy()->endOfDay();
        }

        return [$start, $end];
    }

    protected function snapshotMetrics(): array
    {
        $totalMembers = MemberDetail::count();
        $activeMembers = MemberDetail::where('status', 'Active')->count();
        $inactiveMembers = MemberDetail::where('status', 'Inactive')->count();
        $delinquentMembers = MemberDetail::where('status', 'Delinquent')->count();
        $totalLoans = LoanApplication::count();
        $pendingLoans = LoanApplication::where('status', 'Pending')->count();
        $activeLoans = LoanAccount::where('status', 'Active')->count();
        $totalPayments = CollectionAndPosting::where('status', 'Posted')->count();
        $remainingBalance = (float) LoanAccount::where('status', 'Active')->sum('balance');

        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'inactive_members' => $inactiveMembers,
            'delinquent_members' => $delinquentMembers,
            'total_loans' => $totalLoans,
            'pending_loans' => $pendingLoans,
            'active_loans' => $activeLoans,
            'total_payments' => $totalPayments,
            'remaining_balance' => $remainingBalance,
        ];
    }

    protected function summaryCards(array $snapshot): array
    {
        return [
            [
                'label' => 'Total Members',
                'value' => number_format($snapshot['total_members']),
                'icon' => 'people-outline',
                'color' => '#16a34a',
                'bg' => '#dcfce7',
                'accentColor' => '#16a34a',
            ],
            [
                'label' => 'Total Loans',
                'value' => number_format($snapshot['total_loans']),
                'icon' => 'cash-outline',
                'color' => '#16a34a',
                'bg' => '#dcfce7',
                'accentColor' => '#16a34a',
            ],
            [
                'label' => 'Total Payments',
                'value' => number_format($snapshot['total_payments']),
                'icon' => 'wallet-outline',
                'color' => '#16a34a',
                'bg' => '#dcfce7',
                'accentColor' => '#16a34a',
            ],
            [
                'label' => 'Pending Loans',
                'value' => number_format($snapshot['pending_loans']),
                'icon' => 'time-outline',
                'color' => '#b45309',
                'bg' => '#fef3c7',
                'accentColor' => '#f59e0b',
            ],
        ];
    }

    protected function periodMetrics(Carbon $start, Carbon $end): array
    {
        $months = collect(CarbonPeriod::create($start->copy()->startOfMonth(), '1 month', $end->copy()->startOfMonth()))
            ->mapWithKeys(function (Carbon $month): array {
                return [
                    $month->format('Y-m') => [
                        'key' => $month->format('Y-m'),
                        'label' => $month->format('M Y'),
                        'collections' => 0.0,
                        'loan_releases' => 0.0,
                    ],
                ];
            });

        $collections = CollectionAndPosting::query()
            ->where('status', 'Posted')
            ->whereBetween('payment_date', [$start, $end])
            ->get(['payment_date', 'created_at', 'amount_paid']);

        foreach ($collections as $collection) {
            $date = Carbon::parse($collection->payment_date ?? $collection->created_at);
            $key = $date->format('Y-m');

            if (! $months->has($key)) {
                continue;
            }

            $bucket = $months->get($key);
            $bucket['collections'] += (float) $collection->amount_paid;
            $months->put($key, $bucket);
        }

        $approvedLoans = LoanApplication::query()
            ->where('status', 'Approved')
            ->where(function ($query) use ($start, $end): void {
                $query->whereBetween('approved_at', [$start, $end])
                    ->orWhere(function ($fallbackQuery) use ($start, $end): void {
                        $fallbackQuery->whereNull('approved_at')
                            ->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->get(['approved_at', 'created_at', 'amount_requested']);

        foreach ($approvedLoans as $loanApplication) {
            $date = Carbon::parse($loanApplication->approved_at ?? $loanApplication->created_at);
            $key = $date->format('Y-m');

            if (! $months->has($key)) {
                continue;
            }

            $bucket = $months->get($key);
            $bucket['loan_releases'] += (float) $loanApplication->amount_requested;
            $months->put($key, $bucket);
        }

        $series = $months->values();

        $collectionsTotal = (float) $series->sum('collections');
        $loanReleasesTotal = (float) $series->sum('loan_releases');

        return [
            'totals' => [
                'collections' => round($collectionsTotal, 2),
                'loan_releases' => round($loanReleasesTotal, 2),
            ],
            'chart' => [
                'title' => 'Monthly Collections vs Loan Releases',
                'type' => 'bar',
                'labels' => $series->pluck('label')->all(),
                'datasets' => [
                    [
                        'label' => 'Collections',
                        'data' => $series->pluck('collections')->map(fn (mixed $value): float => round((float) $value, 2))->all(),
                        'color' => '#16a34a',
                    ],
                    [
                        'label' => 'Loan Releases',
                        'data' => $series->pluck('loan_releases')->map(fn (mixed $value): float => round((float) $value, 2))->all(),
                        'color' => '#0f766e',
                    ],
                ],
                'totals' => [
                    'collections' => round($collectionsTotal, 2),
                    'loan_releases' => round($loanReleasesTotal, 2),
                ],
            ],
        ];
    }

    protected function currency(float $value): string
    {
        return '₱'.number_format($value, 2);
    }
}
