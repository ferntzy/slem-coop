<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CollectionsChart;
use App\Filament\Widgets\CollectionsTodayWidget;
use App\Filament\Widgets\LoanApplicationsChart;
use App\Filament\Widgets\LoanOfficerDecisionTrendChart;
use App\Filament\Widgets\LoanOfficerGreetingWidget;
use App\Filament\Widgets\LoanOfficerPipelineStatsWidget;
use App\Filament\Widgets\LoanOfficerPriorityQueueWidget;
use App\Filament\Widgets\LoanPortfolioChart;
use App\Filament\Widgets\MemberGreetingWidget;
use App\Filament\Widgets\MemberLoanScheduleWidget;
use App\Filament\Widgets\MemberLoanStatusChart;
use App\Filament\Widgets\MemberPaymentHistoryChart;
use App\Filament\Widgets\MemberRecentTransactionsWidget;
use App\Filament\Widgets\MemberStatusChart;
use App\Filament\Widgets\MemberUpcomingDueDatesWidget;
use App\Filament\Widgets\RecentLoanApplicationsWidget;
use App\Filament\Widgets\ShareCapitalChart;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public string $period = 'monthly';

    public function getWidgets(): array
    {
        $user = Auth::user();

        // ── Member dashboard ──────────────────────────────────────────────────
        if ($user->isMember()) {
            return [
                // Row 0: Greeting banner
                MemberGreetingWidget::class,

                // Row 1: 3 KPI stat cards (Total Loan Balance, Total Payments, Pending Apps)
                StatsOverviewWidget::class,

                // Row 2: Loan Status donut (4 cols) + Payment History bar chart (8 cols)
                MemberLoanStatusChart::class,
                MemberPaymentHistoryChart::class,

                // Row 3: Recent Transactions (6 cols) + Upcoming Due Dates (6 cols)
                MemberRecentTransactionsWidget::class,
                MemberUpcomingDueDatesWidget::class,

                // Row 4: Share Capital Chart (full width)
                ShareCapitalChart::class,

                // Row 5: Full Loan Payment Schedule (full width)
                MemberLoanScheduleWidget::class,
            ];
        }

        // ── Loan Officer dashboard ───────────────────────────────────────────
        if ($this->isLoanOfficerDashboardUser($user)) {
            return [
                LoanOfficerGreetingWidget::class,
                LoanOfficerPipelineStatsWidget::class,
                LoanOfficerDecisionTrendChart::class,
                LoanOfficerPriorityQueueWidget::class,
                RecentLoanApplicationsWidget::class,
            ];
        }

        // ── Admin / Staff dashboard (original — untouched) ────────────────────

        $widgets = [];

        // ── Row 1: KPI Stats (full width, 6 cards) ──────────────────
        $widgets[] = StatsOverviewWidget::class;

        // ── Row 2: Hero chart (8 cols) + supporting doughnut (4 cols)
        $widgets[] = CollectionsChart::class;
        $widgets[] = LoanApplicationsChart::class;

        // ── Row 3: Two equal charts (6 cols each) ────────────────────
        $widgets[] = MemberStatusChart::class;
        $widgets[] = LoanPortfolioChart::class;

        // ── Row 4: Share Capital chart ────────────────────────────────
        $widgets[] = ShareCapitalChart::class;

        if ($user->hasAnyRole(['admin', 'cashier', 'teller', 'cash_handler', 'branch_manager'])) {
            $widgets[] = CollectionsTodayWidget::class;
        }

        // ── Row 5: Full-width loan applications table ─────────────────
        if ($user->hasAnyRole(['admin', 'loan_officer', 'loan_manager', 'credit_committee', 'branch_manager'])) {
            $widgets[] = RecentLoanApplicationsWidget::class;
        }

        return $widgets;
    }

    protected function isLoanOfficerDashboardUser(User $user): bool
    {
        if ($user->isAdminOrSuperAdmin()) {
            return false;
        }

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setPeriod')
                ->label(match ($this->period) {
                    'weekly' => 'Weekly',
                    'quarterly' => 'Quarterly',
                    'annual' => 'Annual',
                    default => 'Monthly',
                })
                ->color('gray')
                ->outlined()
                ->form([
                    Select::make('period')
                        ->label('Reporting Period')
                        ->options([
                            'weekly' => 'This Week',
                            'monthly' => 'This Month',
                            'quarterly' => 'This Quarter',
                            'annual' => 'This Year',
                        ])
                        ->default($this->period)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->period = $data['period'];
                    $this->dispatch('periodChanged', period: $this->period);
                }),
        ];
    }

    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 4,
            'lg' => 12,
        ];
    }
}
