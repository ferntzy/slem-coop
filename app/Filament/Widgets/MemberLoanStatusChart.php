<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\MemberDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class MemberLoanStatusChart extends ChartWidget
{
    protected ?string $heading = 'My Loan Status';

    protected static ?int $sort = 3;

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
            'lg' => 4,
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $profileId = $user->profile_id;
        $member = MemberDetail::where('profile_id', $profileId)->first();

        $statuses = ['Approved', 'Pending', 'Under Review', 'Rejected', 'Cancelled'];
        $counts = [];

        foreach ($statuses as $status) {
            $counts[] = $member
                ? LoanApplication::where('member_id', $member->id)
                    ->where('status', $status)
                    ->count()
                : 0;
        }

        return [
            'datasets' => [
                [
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.8)',  // Approved  - green
                        'rgba(245, 158, 11, 0.8)',  // Pending   - amber
                        'rgba(59, 130, 246, 0.8)',  // Under Review - blue
                        'rgba(239, 68, 68, 0.8)',   // Rejected  - red
                        'rgba(107, 114, 128, 0.8)', // Cancelled - gray
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $statuses,
        ];
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
