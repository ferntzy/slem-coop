<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\LoanAccount;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class MemberUpcomingDueDatesWidget extends BaseWidget
{
    protected static ?string $heading = 'Upcoming Due Dates';
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        return Auth::user()->isMember();
    }

    public function getColumnSpan(): int | string | array
    {
        return [
            'default' => 1,
            'sm'      => 2,
            'md'      => 4,
            'lg'      => 6,
        ];
    }

    public function table(Table $table): Table
    {
        $profileId = Auth::user()->profile_id;

        return $table
            ->query(
                LoanAccount::query()
                    ->where('profile_id', $profileId)
                    ->where('status', 'Active')
                    ->whereNotNull('maturity_date')
                    ->orderBy('maturity_date', 'asc')
            )
            ->columns([
                TextColumn::make('loan_account_id')
                    ->label('Loan #')
                    ->formatStateUsing(fn ($state) => 'LA-' . str_pad($state, 5, '0', STR_PAD_LEFT)),

                TextColumn::make('monthly_amortization')
                    ->label('Monthly Due')
                    ->money('PHP'),

                TextColumn::make('balance')
                    ->label('Remaining Balance')
                    ->money('PHP')
                    ->color('warning'),

                TextColumn::make('maturity_date')
                    ->label('Maturity Date')
                    ->date('M d, Y')
                    ->sortable()
                    ->color(fn ($record): string =>
                        Carbon::parse($record->maturity_date)->isPast() ? 'danger' :
                        (Carbon::parse($record->maturity_date)->diffInDays(now()) <= 30 ? 'warning' : 'success')
                    ),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active'   => 'success',
                        'Overdue'  => 'danger',
                        'Closed'   => 'gray',
                        default    => 'warning',
                    }),
            ])
            ->striped()
            ->paginated([5, 10])
            ->emptyStateIcon('heroicon-o-calendar')
            ->emptyStateHeading('No active loans')
            ->emptyStateDescription('You have no active loans with upcoming due dates.');
    }
}