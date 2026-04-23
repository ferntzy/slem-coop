<?php

namespace App\Filament\Widgets;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class MemberRecentTransactionsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Transactions';

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return Auth::user()->isMember();
    }

    public function getColumnSpan(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 4,
            'lg' => 6,
        ];
    }

    public function table(Table $table): Table
    {
        $profileId = Auth::user()->profile_id;
        $loanAccountIds = LoanAccount::where('profile_id', $profileId)
            ->pluck('loan_account_id');

        return $table
            ->query(
                CollectionAndPosting::query()
                    ->whereIn('loan_account_id', $loanAccountIds)
                    ->latest('payment_date')
            )
            ->columns([
                TextColumn::make('payment_date')
                    ->label('Date')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('loanAccount.loan_account_id')
                    ->label('Loan #')
                    ->formatStateUsing(fn ($state) => 'LA-'.str_pad($state, 5, '0', STR_PAD_LEFT)),

                TextColumn::make('amount_paid')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->color('info'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Posted' => 'success',
                        'Pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('payment_date', 'desc')
            ->striped()
            ->paginated([5, 10])
            ->emptyStateIcon('heroicon-o-inbox')
            ->emptyStateHeading('No transactions yet')
            ->emptyStateDescription('Your payment transactions will appear here.');
    }
}
