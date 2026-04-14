<?php

namespace App\Filament\Resources\ShareCapitalTransactions;

use App\Filament\Resources\ShareCapitalTransactions\Pages\CreateShareCapitalTransaction;
use App\Filament\Resources\ShareCapitalTransactions\Pages\EditShareCapitalTransaction;
use App\Filament\Resources\ShareCapitalTransactions\Pages\ListShareCapitalTransactions;
use App\Filament\Resources\ShareCapitalTransactions\Schemas\ShareCapitalTransactionForm;
use App\Filament\Resources\ShareCapitalTransactions\Tables\ShareCapitalTransactionsTable;
use App\Models\ShareCapitalTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShareCapitalTransactionResource extends Resource
{
    protected static ?string $model = ShareCapitalTransaction::class;

    protected static bool $shouldRegisterNavigation = false;
    // protected static string|BackedEnum|null $navigationIcon =  Heroicon::OutlinedCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'For Deletion';

    protected static ?string $recordTitleAttribute = 'reference_no';

    public static function form(Schema $schema): Schema
    {
        return ShareCapitalTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShareCapitalTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->latest('transaction_date')->latest('id');
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdminOrSuperAdmin()) {
            return $query;
        }

        if ($user->isMember()) {
            return $query->where('profile_id', $user->profile_id);
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('profile.memberDetail', fn (Builder $memberDetailQuery): Builder => $memberDetailQuery->where('branch_id', $branchId)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShareCapitalTransactions::route('/'),
            'create' => CreateShareCapitalTransaction::route('/create'),
            // 'edit' => EditShareCapitalTransaction::route('/{record}/edit'),
        ];
    }
}
