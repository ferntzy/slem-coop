<?php

namespace App\Filament\Widgets;

use Livewire\Attributes\On;
use Filament\Widgets\ChartWidget;
use App\Models\LoanApplication;
use Illuminate\Support\Facades\Auth;

class LoanApplicationsChart extends ChartWidget
{
    protected ?string $heading = 'Loan Applications by Status';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '320px';

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
            'lg'      => 4,
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
        [$start, $end] = $this->getPeriodRange($filter);

        $statuses = ['Pending', 'Under Review', 'Approved', 'Rejected', 'Cancelled'];
        $counts   = [];

        foreach ($statuses as $status) {
            $counts[] = LoanApplication::where('status', $status)
                ->whereBetween('created_at', [$start, $end])
                ->count();
        }

        return [
            'datasets' => [
                [
                    'data'            => $counts,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(107, 114, 128, 0.8)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $statuses,
        ];
    }

    protected function getPeriodRange(string $filter): array
    {
        return match ($filter) {
            'weekly'    => [now()->startOfWeek(),    now()->endOfWeek()],
            'quarterly' => [now()->firstOfQuarter(), now()->lastOfQuarter()],
            'annual'    => [now()->startOfYear(),    now()->endOfYear()],
            default     => [now()->startOfMonth(),   now()->endOfMonth()],
        };
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
            'cutout' => '65%',
        ];
    }
}