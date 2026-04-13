<?php

namespace App\Filament\Resources\LoanTypes;

use App\Filament\Resources\LoanTypes\Pages\CreateLoanType;
use App\Filament\Resources\LoanTypes\Pages\EditLoanType;
use App\Filament\Resources\LoanTypes\Pages\ListLoanTypes;
use App\Filament\Resources\LoanTypes\Schemas\LoanTypeForm;
use App\Filament\Resources\LoanTypes\Tables\LoanTypesTable;
use App\Models\LoanType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoanTypeResource extends Resource
{
    protected static ?string $model = LoanType::class;

    protected static bool $shouldRegisterNavigation = false;
    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'For Deletion';

    protected static ?int $navigationSort = 9999;

    public static function form(Schema $schema): Schema
    {
        return LoanTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanTypesTable::configure($table);
    }



    public static function getPages(): array
    {
        return [
            'index' => ListLoanTypes::route('/'),
            'create' => CreateLoanType::route('/create'),
            'edit' => EditLoanType::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\LoanTypes\RelationManagers\LoanTypeRequirementsRelationManager::class,
        ];
    }
}
