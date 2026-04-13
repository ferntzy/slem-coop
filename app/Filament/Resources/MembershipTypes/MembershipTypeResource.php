<?php

namespace App\Filament\Resources\MembershipTypes;

use App\Filament\Resources\MembershipTypes\Pages\CreateMembershipType;
use App\Filament\Resources\MembershipTypes\Pages\EditMembershipType;
use App\Filament\Resources\MembershipTypes\Pages\ListMembershipTypes;
use App\Filament\Resources\MembershipTypes\Schemas\MembershipTypeForm;
use App\Filament\Resources\MembershipTypes\Tables\MembershipTypesTable;
use App\Models\MembershipType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MembershipTypeResource extends Resource
{
    protected static ?string $model = MembershipType::class;

    protected static bool $shouldRegisterNavigation = false;
    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|\UnitEnum|null $navigationGroup = 'For Deletion';

    protected static ?int $navigationSort = 9999;

    protected static ?string $recordTitleAttribute = 'name';

    // protected static ?string $modelLabel = 'Settings';

    public static function form(Schema $schema): Schema
    {
        return MembershipTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembershipTypes::route('/'),
            'create' => CreateMembershipType::route('/create'),
            'edit' => EditMembershipType::route('/{record}/edit'),
        ];
    }
}
