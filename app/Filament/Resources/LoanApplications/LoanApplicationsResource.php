<?php

namespace App\Filament\Resources\LoanApplications;

use App\Filament\Resources\LoanApplications\RelationManagers\CashflowsRelationManager;
use App\Filament\Resources\LoanApplications\Schemas\LoanApplicationsForm;
use App\Filament\Resources\LoanApplications\Tables\LoanApplicationsTable;
use App\Models\LoanApplication;
use App\Models\MemberDetail;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LoanApplicationsResource extends Resource
{
    protected static ?string $model = LoanApplication::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Loans';

    protected static ?string $recordTitleAttribute = 'loan_application_id';

    protected static string|\UnitEnum|null $navigationGroup = 'Loan Management';

    public static function form(Schema $schema): Schema
    {
        return LoanApplicationsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoanApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CashflowsRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = LoanApplication::where('status', 'Pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdminOrSuperAdmin()) {
            return $query;
        }

        if ($user->isMember()) {
            $memberId = MemberDetail::where('profile_id', $user->profile_id)
                ->value('id');

            return $query->where('member_id', $memberId);
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoanApplications::route('/'),
            'create' => Pages\CreateLoanApplications::route('/create'),
            'edit' => Pages\EditLoanApplications::route('/{record}/edit'),
        ];
    }
}
