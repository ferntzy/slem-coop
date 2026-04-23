<?php

namespace App\Filament\Widgets;

use App\Models\CollectionAndPosting;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class CollectionsChart extends ChartWidget
{
    protected ?string $heading = 'Collections Overview';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '320px';

    public static function canView(): bool
    {
        return ! Auth::user()->isMember();
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 4,
            'lg' => 8,
        ];
    }

    protected array $methods = [
        'Cash' => 'rgba(59, 130, 246, 0.75)',
        'Bank Transfer' => 'rgba(16, 185, 129, 0.75)',
        'Bank Deposit' => 'rgba(245, 158, 11, 0.75)',
        'Check' => 'rgba(139, 92, 246, 0.75)',
    ];

    protected array $borderColors = [
        'Cash' => 'rgb(59, 130, 246)',
        'Bank Transfer' => 'rgb(16, 185, 129)',
        'Bank Deposit' => 'rgb(245, 158, 11)',
        'Check' => 'rgb(139, 92, 246)',
    ];

    #[On('periodChanged')]
    public function onPeriodChanged(string $period): void
    {
        $this->filter = $period;
    }

    protected function getData(): array
    {
        $filter = $this->filter ?? 'monthly';

        [$labels, $periods] = $this->getPeriods($filter);

        $datasets = [];

        foreach ($this->methods as $method => $bgColor) {
            $data = [];
            foreach ($periods as [$start, $end]) {
                $data[] = CollectionAndPosting::where('status', 'Posted')
                    ->where('payment_method', $method)
                    ->whereBetween('payment_date', [$start, $end])
                    ->sum('amount_paid');
            }

            $datasets[] = [
                'label' => $method,
                'data' => $data,
                'backgroundColor' => $bgColor,
                'borderColor' => $this->borderColors[$method],
                'borderWidth' => 1,
                'borderRadius' => 4,
            ];
        }

        $voidData = [];
        foreach ($periods as [$start, $end]) {
            $voidData[] = CollectionAndPosting::where('status', 'Void')
                ->whereBetween('payment_date', [$start, $end])
                ->sum('amount_paid');
        }

        $datasets[] = [
            'label' => 'Void',
            'data' => $voidData,
            'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
            'borderColor' => 'rgb(239, 68, 68)',
            'borderWidth' => 1,
            'borderRadius' => 4,
        ];

        return [
            'datasets' => $datasets,
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
                    $labels[] = 'Week '.$w;
                    $periods[] = [$start, $end];
                }
            })(),
        };

        return [$labels, $periods];
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
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => ['stacked' => true],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(v){ return '₱' + v.toLocaleString(); }",
                    ],
                ],
            ],
        ];
    }
}
