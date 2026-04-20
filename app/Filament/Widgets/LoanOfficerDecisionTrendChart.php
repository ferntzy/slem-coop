<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\LoanApplicationStatusLog;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class LoanOfficerDecisionTrendChart extends ChartWidget
{
    protected ?string $heading = 'Loan Officer Workload & Decisions';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '320px';

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user || $user->isMember()) {
            return false;
        }

        return static::isLoanOfficerUser($user);
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    #[On('periodChanged')]
    public function onPeriodChanged(string $period): void
    {
        $this->filter = $period;
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'monthly';
        [$labels, $periods] = $this->getPeriods($filter);

        $submitted = [];
        $approved = [];
        $rejected = [];

        foreach ($periods as [$start, $end]) {
            $submitted[] = (clone $this->scopedLoanApplications())
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $approved[] = (clone $this->scopedStatusLogs())
                ->where('to_status', 'Approved')
                ->whereBetween('changed_at', [$start, $end])
                ->count();

            $rejected[] = (clone $this->scopedStatusLogs())
                ->where('to_status', 'Rejected')
                ->whereBetween('changed_at', [$start, $end])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Submitted',
                    'data' => $submitted,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Approved',
                    'data' => $approved,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Rejected',
                    'data' => $rejected,
                    'borderColor' => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getPeriods(string $filter): array
    {
        $labels = [];
        $periods = [];

        match ($filter) {
            'weekly' => (function () use (&$labels, &$periods) {
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $labels[] = $day->format('D d');
                    $periods[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
            })(),

            'quarterly' => (function () use (&$labels, &$periods) {
                for ($m = 0; $m < 3; $m++) {
                    $date = now()->firstOfQuarter()->addMonths($m);
                    $labels[] = $date->format('M Y');
                    $periods[] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                }
            })(),

            'annual' => (function () use (&$labels, &$periods) {
                for ($m = 1; $m <= 12; $m++) {
                    $date = Carbon::create(now()->year, $m);
                    $labels[] = $date->format('M');
                    $periods[] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                }
            })(),

            default => (function () use (&$labels, &$periods) {
                $weeks = now()->weeksInMonth() ?: 4;
                for ($w = 1; $w <= $weeks; $w++) {
                    $start = now()->startOfMonth()->copy()->addWeeks($w - 1);
                    $end = $start->copy()->addDays(6)->endOfDay();
                    $labels[] = 'Wk '.$w;
                    $periods[] = [$start, $end];
                }
            })(),
        };

        return [$labels, $periods];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['stepSize' => 1],
                ],
            ],
        ];
    }

    protected function scopedLoanApplications(): Builder
    {
        $query = LoanApplication::query();
        $user = Auth::user();

        if ($user?->isBranchScoped() && $user->branchId()) {
            $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $user->branchId()));
        }

        return $query;
    }

    protected function scopedStatusLogs(): Builder
    {
        $query = LoanApplicationStatusLog::query();
        $user = Auth::user();

        if ($user?->isBranchScoped() && $user->branchId()) {
            $query->whereHas('application.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $user->branchId()));
        }

        return $query;
    }

    protected static function isLoanOfficerUser(User $user): bool
    {
        return $user->hasAnyRole([
            'loan_officer',
            'Loan Officer',
            'hq_loan_officer',
            'HQ Loan Officer',
            'loan_manager',
            'Loan Manager',
            'credit_committee',
            'Credit Committee',
        ]);
    }
}
