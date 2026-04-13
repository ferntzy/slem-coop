<?php

namespace App\Filament\Resources\RestructureApplications;

use App\Models\LoanPayment;
use App\Filament\Resources\RestructureApplications\Pages\CreateRestructureApplications;
use App\Filament\Resources\RestructureApplications\Pages\EditRestructureApplications;
use App\Filament\Resources\RestructureApplications\Pages\ListRestructureApplications;
use App\Filament\Resources\RestructureApplications\Schemas\RestructureApplicationsForm;
use App\Filament\Resources\RestructureApplications\Tables\RestructureApplicationsTable;
use App\Models\RestructureApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Models\MemberDetail;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\LoanApplication;
use Illuminate\Database\Eloquent\Builder;

class RestructureApplicationsResource extends Resource
{
    
    protected static ?string $model = RestructureApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsVertical;
    
    protected static ?string $navigationLabel = 'Restructure Applications';

    protected static ?string $recordTitleAttribute = 'loan_application_id';
    protected static string|\UnitEnum|null $navigationGroup = 'Loan Management';
    public static function form(Schema $schema): Schema
    {
        return RestructureApplicationsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestructureApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = RestructureApplication::where('status', 'Pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestructureApplications::route('/'),
            'create' => CreateRestructureApplications::route('/create'),
            'edit' => EditRestructureApplications::route('/{record}/edit'),
        ];
    }
    
}
