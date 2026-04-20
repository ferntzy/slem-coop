<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LoanOfficerPipelineStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

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

    protected function getStats(): array
    {
        $baseQuery = $this->scopedLoanApplicationQuery();

        $waitingReviewCount = (clone $baseQuery)
            ->whereIn('status', ['Pending', 'Under Review'])
            ->count();

        $pendingCollateralCount = (clone $baseQuery)
            ->where('collateral_status', 'Pending Verification')
            ->count();

        $approvedAwaitingReleaseCount = (clone $baseQuery)
            ->where('status', 'Approved')
            ->whereDoesntHave('loanAccount')
            ->count();

        $missingPenaltyRuleCount = (clone $baseQuery)
            ->whereIn('status', ['Pending', 'Under Review', 'Approved'])
            ->whereNull('penalty_rule_id')
            ->count();

        return [
            Stat::make('Waiting Review', number_format($waitingReviewCount))
                ->description('Pending and under-review applications')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($waitingReviewCount > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-queue-list'),

            Stat::make('Collateral Checks', number_format($pendingCollateralCount))
                ->description('Applications pending collateral verification')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color($pendingCollateralCount > 0 ? 'info' : 'success')
                ->icon('heroicon-o-shield-exclamation'),

            Stat::make('Ready for Release', number_format($approvedAwaitingReleaseCount))
                ->description('Approved applications with no active loan account yet')
                ->descriptionIcon('heroicon-m-calendar')
                ->color($approvedAwaitingReleaseCount > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Missing Penalty Rule', number_format($missingPenaltyRuleCount))
                ->description('Applications needing penalty rule assignment')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($missingPenaltyRuleCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }

    protected function scopedLoanApplicationQuery(): Builder
    {
        $query = LoanApplication::query();
        $user = Auth::user();

        if ($user?->isBranchScoped() && $user->branchId()) {
            $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $user->branchId()));
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
