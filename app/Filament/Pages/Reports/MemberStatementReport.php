<?php

namespace App\Filament\Pages\Reports;

class MemberStatementReport extends AbstractReportPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Member Statement';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 14;

    protected static ?string $title = 'Member Statement Report';

    protected static ?string $slug = 'reports/member-statement';

    public function mount(): void
    {
        parent::mount();

        if ($this->currentUser()->isMember()) {
            $this->memberId = $this->currentUser()?->profile?->memberDetail?->getKey();
        }
    }

    protected static function reportKey(): string
    {
        return 'member-statement';
    }

    protected static function allowedRoles(): array
    {
        return [
            'super_admin',
            'Super Admin',
            'admin',
            'Admin',
            'manager',
            'Manager',
            'hq_manager',
            'HQ Manager',
            'branch_manager',
            'Branch Manager',
            'cashier',
            'Cashier',
            'teller',
            'Teller',
            'cash_handler',
            'Cash Handler',
            'hq_cashier',
            'HQ Cashier',
            'hq_teller',
            'HQ Teller',
            'loan_officer',
            'Loan Officer',
            'hq_loan_officer',
            'HQ Loan Officer',
            'loan_manager',
            'Loan Manager',
            'credit_committee',
            'Credit Committee',
            'account_officer',
            'Account Officer',
            'hq_account_officer',
            'HQ Account Officer',
            'member',
            'Member',
        ];
    }

    protected function memberSelectDisabled(): bool
    {
        return $this->currentUser()->isMember();
    }
}
