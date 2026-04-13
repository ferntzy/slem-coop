<?php

namespace App\Filament\Widgets;

use Livewire\Attributes\On;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\MemberDetail;
use App\Models\LoanApplication;
use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public function getColumnSpan(): int | string | array
    {
        return 'full';
    }

    public ?string $period = 'monthly';

    #[On('periodChanged')]
    public function onPeriodChanged(string $period): void
    {
        $this->period = $period;
    }

    protected function getStats(): array
    {
        /** @var User $user */
        $user = Auth::user();

        return $user->isMember()
            ? $this->getMemberStats($user)
            : $this->getAdminStats();
    }

    // ── Member-scoped stats ──────────────────────────────────────────────────

    protected function getMemberStats(User $user): array
    {
        $profileId = $user->profile_id;
        $member    = MemberDetail::where('profile_id', $profileId)->first();

        // My Total Loan Balance — sum of all active loan balances
        $totalLoanBalance = $member
            ? LoanAccount::where('profile_id', $profileId)
                ->where('status', 'Active')
                ->sum('balance')
            : 0;

        // My Total Payments — sum of all posted payments
        $totalPayments = $member
            ? CollectionAndPosting::whereHas('loanAccount', function ($q) use ($profileId) {
                $q->where('profile_id', $profileId);
            })
            ->where('status', 'Posted')
            ->sum('amount_paid')
            : 0;

        // My Pending Applications — pending or under review
        $pendingApps = $member
            ? LoanApplication::where('member_id', $member->id)
                ->whereIn('status', ['Pending', 'Under Review'])
                ->count()
            : 0;

        return [
            Stat::make('My Total Loan Balance', '₱' . number_format($totalLoanBalance, 2))
                ->description('Outstanding balance on active loans')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($totalLoanBalance > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('My Total Payments', '₱' . number_format($totalPayments, 2))
                ->description('Total payments made to date')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->icon('heroicon-o-credit-card'),

            Stat::make('My Pending Applications', number_format($pendingApps))
                ->description('Awaiting review or approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingApps > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clipboard-document-list'),
        ];
    }

    // ── Admin/Staff stats (original — untouched) ─────────────────────────────

    protected function getAdminStats(): array
    {
        [$start, $end]         = $this->getPeriodRange();
        [$prevStart, $prevEnd] = $this->getPreviousPeriodRange();

        // Active Members
        $activeMembers     = MemberDetail::where('status', 'Active')->count();
        $prevActiveMembers = MemberDetail::where('status', 'Active')
            ->where('created_at', '<', $prevEnd)
            ->count();
        $memberChange = $prevActiveMembers > 0
            ? round((($activeMembers - $prevActiveMembers) / $prevActiveMembers) * 100, 1)
            : 0;

        // Loan Disbursements this period
        $disbursed     = LoanApplication::where('status', 'Approved')
            ->whereBetween('approved_at', [$start, $end])
            ->sum('amount_requested');
        $prevDisbursed = LoanApplication::where('status', 'Approved')
            ->whereBetween('approved_at', [$prevStart, $prevEnd])
            ->sum('amount_requested');
        $disbursedChange = $prevDisbursed > 0
            ? round((($disbursed - $prevDisbursed) / $prevDisbursed) * 100, 1)
            : 0;

        // Collections this period
        $collected     = CollectionAndPosting::where('status', 'Posted')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount_paid');
        $prevCollected = CollectionAndPosting::where('status', 'Posted')
            ->whereBetween('payment_date', [$prevStart, $prevEnd])
            ->sum('amount_paid');
        $collectedChange = $prevCollected > 0
            ? round((($collected - $prevCollected) / $prevCollected) * 100, 1)
            : 0;

        // Active Loan Accounts
        $activeLoans     = LoanAccount::where('status', 'Active')->count();
        $prevActiveLoans = LoanAccount::where('status', 'Active')
            ->where('created_at', '<', $prevEnd)
            ->count();
        $loansChange = $prevActiveLoans > 0
            ? round((($activeLoans - $prevActiveLoans) / $prevActiveLoans) * 100, 1)
            : 0;

        // Pending Loan Applications
        $pendingApps = LoanApplication::whereIn('status', ['Pending', 'Under Review'])->count();

        // Delinquent Members
        $delinquent = MemberDetail::where('status', 'Delinquent')->count();

        return [
            Stat::make('Active Members', number_format($activeMembers))
                ->description($memberChange >= 0 ? "+{$memberChange}% vs last period" : "{$memberChange}% vs last period")
                ->descriptionIcon($memberChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($memberChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-user-group'),

            Stat::make('Loans Disbursed', '₱' . number_format($disbursed, 2))
                ->description($disbursedChange >= 0 ? "+{$disbursedChange}% vs last period" : "{$disbursedChange}% vs last period")
                ->descriptionIcon($disbursedChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($disbursedChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Collections', '₱' . number_format($collected, 2))
                ->description($collectedChange >= 0 ? "+{$collectedChange}% vs last period" : "{$collectedChange}% vs last period")
                ->descriptionIcon($collectedChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($collectedChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-currency-dollar'),

            Stat::make('Active Loan Accounts', number_format($activeLoans))
                ->description($loansChange >= 0 ? "+{$loansChange}% vs last period" : "{$loansChange}% vs last period")
                ->descriptionIcon($loansChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($loansChange >= 0 ? 'success' : 'danger')
                ->icon('heroicon-o-document-text'),

            Stat::make('Pending Applications', number_format($pendingApps))
                ->description('Awaiting review or approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Delinquent Members', number_format($delinquent))
                ->description('Requires immediate action')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($delinquent > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-circle'),
        ];
    }

    protected function getPeriodRange(): array
    {
        return match ($this->period) {
            'weekly'    => [now()->startOfWeek(),    now()->endOfWeek()],
            'quarterly' => [now()->firstOfQuarter(), now()->lastOfQuarter()],
            'annual'    => [now()->startOfYear(),    now()->endOfYear()],
            default     => [now()->startOfMonth(),   now()->endOfMonth()],
        };
    }

    protected function getPreviousPeriodRange(): array
    {
        return match ($this->period) {
            'weekly'    => [now()->subWeek()->startOfWeek(),       now()->subWeek()->endOfWeek()],
            'quarterly' => [now()->subQuarter()->firstOfQuarter(), now()->subQuarter()->lastOfQuarter()],
            'annual'    => [now()->subYear()->startOfYear(),        now()->subYear()->endOfYear()],
            default     => [now()->subMonth()->startOfMonth(),      now()->subMonth()->endOfMonth()],
        };
    }
}