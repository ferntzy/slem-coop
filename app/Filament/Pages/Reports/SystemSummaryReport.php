<?php

namespace App\Filament\Pages\Reports;

class SystemSummaryReport extends AbstractReportPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'System Summary';

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 12;

    protected static ?string $title = 'System Summary Report';

    protected static ?string $slug = 'reports/system-summary';

    protected static function reportKey(): string
    {
        return 'system-summary';
    }

    protected static function allowedRoles(): array
    {
        return [
            'super_admin',
            'Super Admin',
            'admin',
            'Admin',
        ];
    }
}
