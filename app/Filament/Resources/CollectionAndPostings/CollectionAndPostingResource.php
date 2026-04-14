<?php

namespace App\Filament\Resources\CollectionAndPostings;

use App\Filament\Resources\CollectionAndPostings\Pages\CreateCollectionAndPosting;
use App\Filament\Resources\CollectionAndPostings\Pages\EditCollectionAndPosting;
use App\Filament\Resources\CollectionAndPostings\Pages\ListCollectionAndPostings;
use App\Filament\Resources\CollectionAndPostings\Pages\ViewCollectionAndPosting;
use App\Filament\Resources\CollectionAndPostings\Schemas\CollectionAndPostingForm;
use App\Filament\Resources\CollectionAndPostings\Schemas\CollectionAndPostingInfolist;
use App\Filament\Resources\CollectionAndPostings\Tables\CollectionAndPostingsTable;
use App\Models\CollectionAndPosting;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CollectionAndPostingResource extends Resource
{
    protected static ?string $model = CollectionAndPosting::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Payment Management';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Payments';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdminOrSuperAdmin()) {
            return $query;
        }

        if ($user->hasRole('Member')) {
            return $query->whereHas('loanAccount', function (Builder $q) use ($user) {
                $q->where('profile_id', $user->profile_id);
            });
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('loanAccount.loanApplication.member', fn (Builder $memberQuery): Builder => $memberQuery->where('branch_id', $branchId)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return CollectionAndPostingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CollectionAndPostingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionAndPostingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollectionAndPostings::route('/'),
            'create' => CreateCollectionAndPosting::route('/create'),
            'view' => ViewCollectionAndPosting::route('/{record}'),
            'edit' => EditCollectionAndPosting::route('/{record}/edit'),
        ];
    }
}
