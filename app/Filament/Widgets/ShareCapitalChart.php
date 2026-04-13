<?php

namespace App\Filament\Widgets;

use Livewire\Attributes\On;
use Filament\Widgets\ChartWidget;
use App\Models\ShareCapitalTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ShareCapitalChart extends ChartWidget
{
    protected ?string $heading = 'Share Capital Transactions';
    protected static ?int $sort = 7;
    protected ?string $maxHeight = '320px';

    public function getColumnSpan(): int | string | array
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
        $filter    = $this->filter ?? 'monthly';
        $user      = Auth::user();
        $profileId = $user->isMember() ? $user->profile_id : null;

        [$labels, $dateRanges] = $this->getDateRanges($filter);

        $credits = [];
        $debits  = [];

        foreach ($dateRanges as [$start, $end]) {
            $creditQuery = ShareCapitalTransaction::where('direction', 'credit')
                ->whereBetween('transaction_date', [$start, $end]);

            $debitQuery = ShareCapitalTransaction::where('direction', 'debit')
                ->whereBetween('transaction_date', [$start, $end]);

            // Scope to member's own profile_id if member
            if ($profileId) {
                $creditQuery->where('profile_id', $profileId);
                $debitQuery->where('profile_id', $profileId);
            }

            $credits[] = $creditQuery->sum('amount');
            $debits[]  = $debitQuery->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Credits (₱)',
                    'data'            => $credits,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.75)',
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
                [
                    'label'           => 'Debits (₱)',
                    'data'            => $debits,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.75)',
                    'borderColor'     => 'rgb(239, 68, 68)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getDateRanges(string $filter): array
    {
        $labels = [];
        $ranges = [];

        match ($filter) {
            'weekly' => (function () use (&$labels, &$ranges) {
                for ($i = 6; $i >= 0; $i--) {
                    $day      = now()->subDays($i);
                    $labels[] = $day->format('D d');
                    $ranges[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
            })(),

            'quarterly' => (function () use (&$labels, &$ranges) {
                for ($m = 0; $m < 3; $m++) {
                    $date     = now()->firstOfQuarter()->addMonths($m);
                    $labels[] = $date->format('M Y');
                    $ranges[] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                }
            })(),

            'annual' => (function () use (&$labels, &$ranges) {
                for ($m = 1; $m <= 12; $m++) {
                    $date     = Carbon::create(now()->year, $m);
                    $labels[] = $date->format('M');
                    $ranges[] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                }
            })(),

            default => (function () use (&$labels, &$ranges) {
                $weeks = now()->weeksInMonth() ?: 4;
                for ($w = 1; $w <= $weeks; $w++) {
                    $start    = now()->startOfMonth()->copy()->addWeeks($w - 1);
                    $end      = $start->copy()->addDays(6)->endOfDay();
                    $labels[] = 'Wk ' . $w;
                    $ranges[] = [$start, $end];
                }
            })(),
        };

        return [$labels, $ranges];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'top'],
            ],
            'scales' => [
                'x' => ['stacked' => false],
                'y' => [
                    'beginAtZero' => true,
                    'ticks'       => [
                        'callback' => "function(v){ return '₱' + v.toLocaleString(); }",
                    ],
                ],
            ],
        ];
    }
}