<?php

namespace App\Filament\Pages\Reports;

class DailyCollectionReport extends AbstractReportPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Daily Collection';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected static ?string $title = 'Daily Collection Report';

    protected static ?string $slug = 'reports/daily-collection';

    protected static function reportKey(): string
    {
        return 'daily-collection';
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
            'account_officer',
            'Account Officer',
            'hq_account_officer',
            'HQ Account Officer',
        ];
    }
}
