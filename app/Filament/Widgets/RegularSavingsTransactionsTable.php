<?php

namespace App\Filament\Widgets;

use App\Models\MemberDetail;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RegularSavingsTransactionsTable extends BaseWidget
{
    public ?MemberDetail $record = null;

    protected static ?string $heading = 'Regular Savings Transactions';

    protected static ?string $regularSavingsTypeId = null;

    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Deposit' => 'success',
                        'Withdrawal' => 'warning',
                        'Interest' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->state(function ($record): float {
                        $depositAmount = (float) ($record->deposit ?? 0);

                        if ($depositAmount > 0) {
                            return $depositAmount;
                        }

                        $withdrawalAmount = (float) ($record->withdrawal ?? 0);

                        if ($withdrawalAmount > 0) {
                            return $withdrawalAmount;
                        }

                        return (float) ($record->amount ?? 0);
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('transaction_date')
                    ->label('Transaction Date')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),

                TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->paginationMode(PaginationMode::Default)
            ->paginated([10, 25, 50])
            ->queryStringIdentifier('regularSavingsTransactions')
            ->extremePaginationLinks()
            ->emptyStateHeading('No regular savings transactions yet')
            ->emptyStateDescription('Regular savings activity for this member will appear here.');
    }

    protected function getTableQuery(): Builder
    {
        $regularSavingsTypeId = static::getRegularSavingsTypeId();
        $profileId = $this->record?->profile_id;

        if (! $regularSavingsTypeId || ! $profileId) {
            return $this->getFallbackQuery();
        }

        return $this->record
            ->savingsAccountTransactions()
            ->getQuery()
            ->where('savings_type_id', $regularSavingsTypeId)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');
    }

    protected function getFallbackQuery(): Builder
    {
        return $this->record
            ? $this->record->savingsAccountTransactions()->getQuery()->whereRaw('1 = 0')
            : SavingsAccountTransaction::query()->whereRaw('1 = 0');
    }

    protected static function getRegularSavingsTypeId(): ?string
    {
        if (static::$regularSavingsTypeId !== null) {
            return static::$regularSavingsTypeId;
        }

        $regularSavingsType = SavingsType::query()
            ->where('name', 'Regular Savings')
            ->orWhere('code', 'SA 02')
            ->first();

        return static::$regularSavingsTypeId = $regularSavingsType
            ? (string) $regularSavingsType->getKey()
            : null;
    }
}
