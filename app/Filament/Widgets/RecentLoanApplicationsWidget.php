<?php

namespace App\Filament\Widgets;

use App\Models\LoanApplication;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentLoanApplicationsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Loan Applications';

    protected static ?int $sort = 8;

    public static function canView(): bool
    {
        return ! Auth::user()->isMember();
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LoanApplication::query()
                    ->with(['member.profile', 'type'])
                    ->latest()
                    ->limit(20)
            )
            ->columns([
                TextColumn::make('loan_application_id')
                    ->label('#')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('member.profile.first_name')
                    ->label('Member')
                    ->formatStateUsing(fn ($record) => $record->member?->profile?->first_name.' '.
                        $record->member?->profile?->last_name
                    )
                    ->searchable(query: fn ($query, $search) => $query->whereHas('member.profile', fn ($q) => $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                    )
                    )
                    ->icon('heroicon-o-user'),

                TextColumn::make('type.name')
                    ->label('Loan Type')
                    ->badge()
                    ->color('info'),

                TextColumn::make('application_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'New' => 'success',
                        'Restructure' => 'warning',
                        'Reloan' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('amount_requested')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('term_months')
                    ->label('Term')
                    ->formatStateUsing(fn ($state) => $state.' mos.')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        'Under Review' => 'info',
                        'Rejected' => 'danger',
                        'Cancelled' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('collateral_status')
                    ->label('Collateral')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Pending' => 'warning',
                        'Rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Applied')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Under Review' => 'Under Review',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                        'Cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('application_type')
                    ->label('Type')
                    ->options([
                        'New' => 'New',
                        'Restructure' => 'Restructure',
                        'Reloan' => 'Reloan',
                    ]),
            ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No loan applications found')
            ->poll('30s');
    }
}
