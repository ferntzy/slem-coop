<?php

namespace App\Filament\Widgets;

use Livewire\Attributes\On;
use Filament\Widgets\ChartWidget;
use App\Models\MembershipApplication;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class MemberStatusChart extends ChartWidget
{
    protected ?string $heading = 'Membership Applications Trend';
    protected static ?int $sort = 4;
    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return ! Auth::user()->isMember();
    }

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

        $approved    = [];
        $pending     = [];
        $rejected    = [];
        $needsReview = [];

        foreach ($periods as [$start, $end]) {
            $approved[]    = MembershipApplication::where('status', 'approved')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $pending[]     = MembershipApplication::where('status', 'pending')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $rejected[]    = MembershipApplication::where('status', 'rejected')
                ->whereBetween('created_at', [$start, $end])
                ->count();
            $needsReview[] = MembershipApplication::where('status', 'needs_review')
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label'           => 'Approved',
                    'data'            => $approved,
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Pending',
                    'data'            => $pending,
                    'borderColor'     => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Needs Review',
                    'data'            => $needsReview,
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
                [
                    'label'           => 'Rejected',
                    'data'            => $rejected,
                    'borderColor'     => 'rgb(239, 68, 68)',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getPeriods(string $filter): array
    {
        $labels  = [];
        $periods = [];

        match ($filter) {
            'weekly' => (function () use (&$labels, &$periods) {
                for ($i = 6; $i >= 0; $i--) {
                    $day       = now()->subDays($i);
                    $labels[]  = $day->format('D d');
                    $periods[] = [$day->copy()->startOfDay(), $day->copy()->endOfDay()];
                }
            })(),

            'quarterly' => (function () use (&$labels, &$periods) {
                for ($m = 0; $m < 3; $m++) {
                    $date      = now()->firstOfQuarter()->addMonths($m);
                    $labels[]  = $date->format('M Y');
                    $periods[] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                }
            })(),

            'annual' => (function () use (&$labels, &$periods) {
                for ($m = 1; $m <= 12; $m++) {
                    $date      = Carbon::create(now()->year, $m);
                    $labels[]  = $date->format('M');
                    $periods[] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                }
            })(),

            default => (function () use (&$labels, &$periods) {
                $weeks = now()->weeksInMonth() ?: 4;
                for ($w = 1; $w <= $weeks; $w++) {
                    $start     = now()->startOfMonth()->copy()->addWeeks($w - 1);
                    $end       = $start->copy()->addDays(6)->endOfDay();
                    $labels[]  = 'Wk ' . $w;
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
                    'ticks'       => ['stepSize' => 1],
                ],
            ],
        ];
    }
}