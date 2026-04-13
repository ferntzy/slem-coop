<?php

namespace App\Filament\Resources\MembershipApplications;

use App\Filament\Resources\MembershipApplications\Pages\CreateMembershipApplication;
use App\Filament\Resources\MembershipApplications\Pages\EditMembershipApplication;
use App\Filament\Resources\MembershipApplications\Pages\ListMembershipApplications;
use App\Filament\Resources\MembershipApplications\Pages\ViewMembershipApplication;
use App\Filament\Resources\MembershipApplications\Schemas\MembershipApplicationForm;
use App\Filament\Resources\MembershipApplications\Tables\MembershipApplicationsTable;
use App\Models\MembershipApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MembershipApplicationResource extends Resource
{
    protected static ?string $model = MembershipApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|\UnitEnum|null $navigationGroup = 'Membership Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MembershipApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        // Configure the table and remove the Create button above it
        return MembershipApplicationsTable::configure($table);
           
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = MembershipApplication::where('status', 'Pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMembershipApplications::route('/'),
            'create' => CreateMembershipApplication::route('/create'),
            'view'   => ViewMembershipApplication::route('/{record}'),
            'edit'   => EditMembershipApplication::route('/{record}/edit'),
        ];
    }
    protected static function getTableHeaderActions(): array
    {
        return []; // disables the Create button
    }
}