<?php

namespace App\Filament\Resources\SavingsAccounts;

use App\Filament\Resources\SavingsAccounts\Pages\CreateSavingsAccount;
use App\Filament\Resources\SavingsAccounts\Pages\EditSavingsAccount;
use App\Filament\Resources\SavingsAccounts\Pages\ListSavingsAccounts;
use App\Filament\Resources\SavingsAccounts\Pages\ViewSavingsAccount;
use App\Filament\Resources\SavingsAccounts\RelationManagers\SavingsAccountTransactionsRelationManager;
use App\Filament\Resources\SavingsAccounts\Schemas\SavingsAccountForm;
use App\Filament\Resources\SavingsAccounts\Schemas\SavingsAccountInfolist;
use App\Filament\Resources\SavingsAccounts\Tables\SavingsAccountsTable;
use App\Models\SavingsAccount;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SavingsAccountResource extends Resource
{
    protected static ?string $model = SavingsAccount::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Savings Accounts';

    protected static ?string $modelLabel = 'Savings';

    protected static ?string $pluralModelLabel = 'Savings Accounts';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Savings Management';

    public static function form(Schema $schema): Schema
    {
        return SavingsAccountForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SavingsAccountInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SavingsAccountsTable::table($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isMember()) {
            return $query->whereHas('member', fn (Builder $memberQuery): Builder => $memberQuery->where('profile_id', $user->profile_id));
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('member.memberDetail', fn (Builder $memberDetailQuery): Builder => $memberDetailQuery->where('branch_id', $branchId)
            );
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            SavingsAccountTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSavingsAccounts::route('/'),
            'create' => CreateSavingsAccount::route('/create'),
            'view' => ViewSavingsAccount::route('/{record}'),
            'edit' => EditSavingsAccount::route('/{record}/edit'),
        ];
    }
}
