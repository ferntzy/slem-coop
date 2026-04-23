<?php

namespace App\Filament\Widgets;

use App\Models\MembershipApplication;
use Filament\Widgets\Widget;

class MembershipApplicationsStats extends Widget
{
    protected string $view = 'filament.widgets.membership-applications-stats';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function getStats(): array
    {
        $total = MembershipApplication::count();
        $pending = MembershipApplication::where('status', 'pending')->count();
        $underReview = MembershipApplication::where('status', 'needs_review')->count();
        $approved = MembershipApplication::where('status', 'approved')->count();
        $rejected = MembershipApplication::where('status', 'rejected')->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'under_review' => $underReview,
            'approved' => $approved,
            'rejected' => $rejected,
        ];
    }
}
