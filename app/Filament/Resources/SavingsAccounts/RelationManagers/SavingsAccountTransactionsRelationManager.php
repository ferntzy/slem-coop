<?php

namespace App\Filament\Resources\SavingsAccounts\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SavingsAccountTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Transactions';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('reference_no')
                    ->label('Reference No.')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(40)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('postedBy.username')
                    ->label('Posted By')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('addTransaction')
                    ->label('New Transaction')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('New Savings Transaction')
                    ->form(function (Schema $schema): Schema {
                        return $schema->components([
                            Section::make()
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('direction')
                                            ->options([
                                                'credit' => 'Credit (Deposit)',
                                                'debit' => 'Debit (Withdraw)',
                                            ])
                                            ->default('credit')
                                            ->required()
                                            ->live(),

                                        Select::make('type')
                                            ->options(fn (callable $get): array => $get('direction') === 'debit'
                                                ? [
                                                    'withdraw' => 'Withdraw',
                                                    'adjustment_debit' => 'Adjustment (Debit)',
                                                ]
                                                : [
                                                    'deposit' => 'Deposit',
                                                    'adjustment_credit' => 'Adjustment (Credit)',
                                                ])
                                            ->default(fn (callable $get) => $get('direction') === 'debit' ? 'withdraw' : 'deposit')
                                            ->required(),

                                        TextInput::make('amount')
                                            ->numeric()
                                            ->minValue(0.01)
                                            ->prefix('₱')
                                            ->required(),

                                        DatePicker::make('transaction_date')
                                            ->default(now())
                                            ->required(),

                                        TextInput::make('reference_no')
                                            ->label('Reference No.')
                                            ->maxLength(50)
                                            ->nullable(),

                                        Textarea::make('notes')
                                            ->rows(3)
                                            ->maxLength(255)
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                                ]),
                        ]);
                    })
                    ->action(function (array $data): void {
                        $savingsAccount = $this->getOwnerRecord();

                        $amount = (float) ($data['amount'] ?? 0);
                        $direction = (string) ($data['direction'] ?? 'credit');

                        if ($amount <= 0) {
                            throw ValidationException::withMessages([
                                'amount' => 'Amount must be greater than 0.',
                            ]);
                        }

                        if ($direction === 'debit' && $amount > (float) ($savingsAccount->balance ?? 0)) {
                            throw ValidationException::withMessages([
                                'amount' => 'Withdrawal amount cannot exceed the current balance.',
                            ]);
                        }

                        DB::transaction(function () use ($savingsAccount, $data, $amount, $direction): void {
                            $savingsAccount->transactions()->create([
                                'amount' => $amount,
                                'direction' => $direction,
                                'type' => $data['type'] ?? ($direction === 'debit' ? 'withdraw' : 'deposit'),
                                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                                'reference_no' => $data['reference_no'] ?? null,
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ]);

                            $newBalance = $direction === 'debit'
                                ? (float) ($savingsAccount->balance ?? 0) - $amount
                                : (float) ($savingsAccount->balance ?? 0) + $amount;

                            $savingsAccount->update([
                                'balance' => $newBalance,
                            ]);
                        });

                        Notification::make()
                            ->title('Transaction saved.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->actions([])
            ->bulkActions([]);
    }
}

