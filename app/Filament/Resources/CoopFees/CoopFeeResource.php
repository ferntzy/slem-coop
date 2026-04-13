<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoopFeeResource\Pages;
use App\Filament\Resources\CoopFeeResource\Forms\CoopFeeForm;
use App\Filament\Resources\CoopFeeResource\Tables\CoopFeesTable;
use App\Models\CoopFee;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CoopFeeResource extends Resource
{
    protected static ?string $model = CoopFee::class;

    // protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationLabel = 'Coop Fees';
    protected static ?string $pluralLabel = 'Coop Fees';

    public static function form(Schema $schema): Schema
    {
        return CoopFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoopFeesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoopFees::route('/'),
            'create' => Pages\CreateCoopFee::route('/create'),
            'edit' => Pages\EditCoopFee::route('/{record}/edit'),
        ];
    }
}
