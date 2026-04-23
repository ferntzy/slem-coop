<?php

namespace App\Filament\Resources\RestructureApplications;

use App\Filament\Resources\RestructureApplications\Pages\CreateRestructureApplications;
use App\Filament\Resources\RestructureApplications\Pages\EditRestructureApplications;
use App\Filament\Resources\RestructureApplications\Pages\ListRestructureApplications;
use App\Filament\Resources\RestructureApplications\Schemas\RestructureApplicationsForm;
use App\Filament\Resources\RestructureApplications\Tables\RestructureApplicationsTable;
use App\Models\RestructureApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RestructureApplicationsResource extends Resource
{
    protected static ?string $model = RestructureApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsVertical;

    protected static ?string $navigationLabel = 'Restructure Applications';

    protected static ?string $recordTitleAttribute = 'loan_application_id';

    protected static string|\UnitEnum|null $navigationGroup = 'Loan Management';

    public static function form(Schema $schema): Schema
    {
        return RestructureApplicationsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestructureApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = RestructureApplication::where('status', 'Pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->isAdminOrSuperAdmin() || $user->isHeadOffice()) {
            return $query;
        } // ← fix

        if ($user->isMember()) {
            return $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('profile_id', $user->profile_id)
            );
        }

        if ($user->isBranchScoped()) {
            $branchId = $user->branchId();

            if (! $branchId) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('branch_id', $branchId)
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestructureApplications::route('/'),
            'create' => CreateRestructureApplications::route('/create'),
            'edit' => EditRestructureApplications::route('/{record}/edit'),
        ];
    }
}
