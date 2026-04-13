<?php

namespace App\Filament\Widgets;
use Livewire\Attributes\On;
use Filament\Widgets\ChartWidget;
use App\Models\LoanAccount;
use Illuminate\Support\Carbon;

class LoanPortfolioChart extends ChartWidget
{
    protected ?string $heading = 'Loan Portfolio — Outstanding Balance';
    protected static ?int $sort = 5;
    protected ?string $maxHeight = '300px';

    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'md'      => 2,
            'lg'      => 6,
        ];
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

        $active      = [];
        $defaulted   = [];
        $completed   = [];
        $restructured = [];

        foreach ($periods as $end) {
            $active[]       = LoanAccount::where('status', 'Active')->where('created_at', '<=', $end)->sum('balance');
            $defaulted[]    = LoanAccount::where('status', 'Defaulted')->where('created_at', '<=', $end)->sum('balance');
            $completed[]    = LoanAccount::where('status', 'Completed')->where('created_at', '<=', $end)->count();
            $restructured[] = LoanAccount::where('status', 'Restructured')->where('created_at', '<=', $end)->sum('balance');
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Active Balance (₱)',
                    'data'            => $active,
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Defaulted Balance (₱)',
                    'data'            => $defaulted,
                    'borderColor'     => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Restructured Balance (₱)',
                    'data'            => $restructured,
                    'borderColor'     => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                    'yAxisID'         => 'y',
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
                    $day = now()->subDays($i)->endOfDay();
                    $labels[]  = $day->format('D d');
                    $periods[] = $day;
                }
            })(),
            'quarterly' => (function () use (&$labels, &$periods) {
                for ($m = 0; $m < 3; $m++) {
                    $date = now()->firstOfQuarter()->addMonths($m)->endOfMonth();
                    $labels[]  = $date->format('M Y');
                    $periods[] = $date;
                }
            })(),
            'annual' => (function () use (&$labels, &$periods) {
                for ($m = 1; $m <= 12; $m++) {
                    $date = Carbon::create(now()->year, $m)->endOfMonth();
                    $labels[]  = $date->format('M');
                    $periods[] = $date;
                }
            })(),
            default => (function () use (&$labels, &$periods) {
                $weeks = now()->weeksInMonth() ?: 4;
                for ($w = 1; $w <= $weeks; $w++) {
                    $date = now()->startOfMonth()->addWeeks($w)->subDay()->endOfDay();
                    $labels[]  = 'Wk ' . $w;
                    $periods[] = $date;
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
                    'ticks' => [
                        'callback' => "function(v){ return '₱' + v.toLocaleString(); }",
                    ],
                ],
            ],
        ];
    }
}
