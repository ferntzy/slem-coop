<?php

namespace App\Filament\Resources\PenaltyRules;

use App\Filament\Resources\PenaltyRules\Pages\CreatePenaltyRule;
use App\Filament\Resources\PenaltyRules\Pages\EditPenaltyRule;
use App\Filament\Resources\PenaltyRules\Pages\ListPenaltyRules;
use App\Filament\Resources\PenaltyRules\Schemas\PenaltyRuleForm;
use App\Filament\Resources\PenaltyRules\Tables\PenaltyRulesTable;
use App\Models\PenaltyRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PenaltyRuleResource extends Resource
{
    protected static ?string $model = PenaltyRule::class;

    protected static bool $shouldRegisterNavigation = false;
    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|\UnitEnum|null $navigationGroup = 'For Deletion';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Penalty Rules';

    protected static ?string $modelLabel = 'Penalty Rule';

    protected static ?string $pluralModelLabel = 'Penalty Rules';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return PenaltyRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PenaltyRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPenaltyRules::route('/'),
            'create' => CreatePenaltyRule::route('/create'),
            'edit'   => EditPenaltyRule::route('/{record}/edit'),
        ];
    }
}
