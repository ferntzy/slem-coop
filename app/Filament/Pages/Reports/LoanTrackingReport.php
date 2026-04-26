<?php

namespace App\Filament\Pages\Reports;

class LoanTrackingReport extends AbstractReportPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Loan Tracking';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 11;

    protected static ?string $title = 'Loan Tracking Report';

    protected static ?string $slug = 'reports/loan-tracking';

    protected static function reportKey(): string
    {
        return 'loan-tracking';
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
        ];
    }
}
