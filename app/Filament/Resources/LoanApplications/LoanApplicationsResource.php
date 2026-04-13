<?php

namespace App\Filament\Resources\LoanApplications;

use App\Filament\Resources\LoanApplications\Pages;
use App\Filament\Resources\LoanApplications\RelationManagers\CashflowsRelationManager;
use App\Filament\Resources\LoanApplications\Schemas\LoanApplicationsForm;
use App\Filament\Resources\LoanApplications\Tables\LoanApplicationsTable;
use App\Models\LoanApplication;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    $query = parent::getEloquentQuery();

    if (auth()->user()?->hasRole('Member')) {
        $memberId = \App\Models\MemberDetail::where(
            'profile_id',
            auth()->user()->profile_id
        )->value('id');


        $query->where('member_id', $memberId);
    }

    return $query;
}

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLoanApplications::route('/'),
            'create' => Pages\CreateLoanApplications::route('/create'),
            'edit'   => Pages\EditLoanApplications::route('/{record}/edit'),
        ];
    }
}
