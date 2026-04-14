<?php

namespace App\Filament\Resources\MemberDetails;

use App\Filament\Resources\MemberDetails\Pages\CreateMemberDetail;
use App\Filament\Resources\MemberDetails\Pages\EditMemberDetail;
use App\Filament\Resources\MemberDetails\Pages\ListMemberDetails;
use App\Filament\Resources\MemberDetails\Pages\ViewMemberDetail;
use App\Filament\Resources\MemberDetails\RelationManagers\LoanHistoryRelationManager;
use App\Filament\Resources\MemberDetails\Schemas\MemberDetailForm;
use App\Filament\Resources\MemberDetails\Schemas\MemberDetailInfolist;
use App\Filament\Resources\MemberDetails\Tables\MemberDetailsTable;
use App\Models\MemberDetail;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberDetailResource extends Resource
{
    protected static ?string $model = MemberDetail::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Members';

    protected static string|\UnitEnum|null $navigationGroup = 'Membership Management';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MemberDetailForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MemberDetailInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberDetailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LoanHistoryRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1=0');
        }

        if ($user->isAdminOrSuperAdmin()) {
            return $query;
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                return $query->whereRaw('1=0');
            }

            return $query->where('branch_id', $branchId);
        }

        return $query->whereRaw('1=0');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberDetails::route('/'),
            'create' => CreateMemberDetail::route('/create'),
            'view' => ViewMemberDetail::route('/{record}'),
            'edit' => EditMemberDetail::route('/{record}/edit'),
        ];
    }
}
