<?php

namespace App\Filament\Widgets;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class MemberPaymentHistoryChart extends ChartWidget
{
    protected ?string $heading = 'My Payment History (Monthly)';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '320px';

    public static function canView(): bool
    {
        return Auth::user()->isMember();
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 2,
            'lg' => 8,
        ];
    }

    #[On('periodChanged')]
    public function onPeriodChanged(string $period): void
    {
        $this->filter = $period;
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $profileId = $user->profile_id;

        // Get all loan account IDs belonging to this member
        $loanAccountIds = LoanAccount::where('profile_id', $profileId)
            ->pluck('loan_account_id');

        // Build last 12 months labels and ranges
        $labels = [];
        $amounts = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $amounts[] = CollectionAndPosting::whereIn('loan_account_id', $loanAccountIds)
                ->where('status', 'Posted')
                ->whereBetween('payment_date', [$start, $end])
                ->sum('amount_paid');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Amount Paid (₱)',
                    'data' => $amounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.75)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['grid' => ['display' => false]],
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
