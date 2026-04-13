<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
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
                \App\Filament\Widgets\MemberGreetingWidget::class,

                // Row 1: 3 KPI stat cards (Total Loan Balance, Total Payments, Pending Apps)
                \App\Filament\Widgets\StatsOverviewWidget::class,

                // Row 2: Loan Status donut (4 cols) + Payment History bar chart (8 cols)
                \App\Filament\Widgets\MemberLoanStatusChart::class,
                \App\Filament\Widgets\MemberPaymentHistoryChart::class,

                // Row 3: Recent Transactions (6 cols) + Upcoming Due Dates (6 cols)
                \App\Filament\Widgets\MemberRecentTransactionsWidget::class,
                \App\Filament\Widgets\MemberUpcomingDueDatesWidget::class,

                // Row 4: Share Capital Chart (full width)
                \App\Filament\Widgets\ShareCapitalChart::class,

                // Row 5: Full Loan Payment Schedule (full width)
                \App\Filament\Widgets\MemberLoanScheduleWidget::class,
            ];
        }

        // ── Admin / Staff dashboard (original — untouched) ────────────────────

        $widgets = [];

        // ── Row 1: KPI Stats (full width, 6 cards) ──────────────────
        $widgets[] = \App\Filament\Widgets\StatsOverviewWidget::class;

        // ── Row 2: Hero chart (8 cols) + supporting doughnut (4 cols)
        $widgets[] = \App\Filament\Widgets\CollectionsChart::class;
        $widgets[] = \App\Filament\Widgets\LoanApplicationsChart::class;

        // ── Row 3: Two equal charts (6 cols each) ────────────────────
        $widgets[] = \App\Filament\Widgets\MemberStatusChart::class;
        $widgets[] = \App\Filament\Widgets\LoanPortfolioChart::class;

        // ── Row 4: Share Capital chart ────────────────────────────────
        $widgets[] = \App\Filament\Widgets\ShareCapitalChart::class;

        if ($user->hasAnyRole(['admin', 'cashier', 'teller', 'cash_handler', 'branch_manager'])) {
            $widgets[] = \App\Filament\Widgets\CollectionsTodayWidget::class;
        }

        // ── Row 5: Full-width loan applications table ─────────────────
        if ($user->hasAnyRole(['admin', 'loan_officer', 'loan_manager', 'credit_committee', 'branch_manager'])) {
            $widgets[] = \App\Filament\Widgets\RecentLoanApplicationsWidget::class;
        }

        return $widgets;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('setPeriod')
                ->label(match ($this->period) {
                    'weekly'    => 'Weekly',
                    'quarterly' => 'Quarterly',
                    'annual'    => 'Annual',
                    default     => 'Monthly',
                })
                ->color('gray')
                ->outlined()
                ->form([
                    Select::make('period')
                        ->label('Reporting Period')
                        ->options([
                            'weekly'    => 'This Week',
                            'monthly'   => 'This Month',
                            'quarterly' => 'This Quarter',
                            'annual'    => 'This Year',
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

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'md'      => 4,
            'lg'      => 12,
        ];
    }
}