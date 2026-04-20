<?php

namespace App\Filament\Resources\MemberDetails\Tables;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use App\Models\Profile;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MemberDetailsTable
{
    protected static function getSavingsType(callable $get): ?SavingsType
    {
        $typeId = $get('savings_type_id');

        if (! $typeId) {
            return null;
        }

        return SavingsType::find($typeId);
    }

    protected static function money(?float $amount): string
    {
        return $amount !== null ? '₱'.number_format($amount, 2) : '—';
    }

    protected static function getRegularSavingsBalance(int $profileId): float
    {
        if (! $profileId) {
            return 0;
        }

        $transactions = SavingsAccountTransaction::where('profile_id', $profileId)
            ->where('savings_type_id', 2)
            ->get();

        $totalDeposit = (float) $transactions->sum('deposit');
        $totalWithdrawal = (float) $transactions->sum('withdrawal');

        return max($totalDeposit - $totalWithdrawal, 0);
    }

    protected static function getTimeDepositMaturityDate(?SavingsAccountTransaction $transaction): ?Carbon
    {
        if (! $transaction || ! $transaction->transaction_date || ! $transaction->terms) {
            return null;
        }

        return Carbon::parse($transaction->transaction_date)
            ->addMonths((int) $transaction->terms);
    }

    protected static function isTimeDepositMatured(?SavingsAccountTransaction $transaction): bool
    {
        $maturityDate = static::getTimeDepositMaturityDate($transaction);

        if (! $maturityDate) {
            return false;
        }

        return now()->greaterThanOrEqualTo($maturityDate);
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(fn ($record) => MemberDetailResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('profile.full_name')
                    ->label('Member')
                    ->searchable(
                        query: function (Builder $query, string $search): Builder {
                            return $query->whereHas('profile', function (Builder $q) use ($search) {
                                $q->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('middle_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                            });
                        }
                    )
                    ->sortable(
                        query: function (Builder $query, string $direction): Builder {
                            return $query
                                ->join('profiles', 'profiles.profile_id', '=', 'member_details.profile_id')
                                ->orderBy('profiles.first_name', $direction)
                                ->orderBy('profiles.last_name', $direction);
                        }
                    ),

                TextColumn::make('profile.email')
                    ->label('Login Email')
                    ->searchable(),

                TextColumn::make('membershipType.name')
                    ->label('Membership Type')
                    ->sortable(),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                ImageColumn::make('signature_path')
                    ->disk('public')
                    ->label('Signature')
                    ->height(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->hidden(),

                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),

                    Action::make('add_savings')
                        ->label('Add Savings')
                        ->icon('heroicon-o-building-library')
                        ->color('success')
                        ->form([
                            Select::make('profile_id')
                                ->label('Member')
                                ->options(function ($record) {
                                    return Profile::where('profile_id', $record->profile_id)
                                        ->get()
                                        ->mapWithKeys(function ($profile) {
                                            return [$profile->profile_id => $profile->full_name];
                                        })
                                        ->toArray();
                                })
                                ->default(fn ($record) => $record->profile_id)
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            Select::make('savings_type_id')
                                ->label('Savings Type')
                                ->options(function () {
                                    return SavingsType::where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($type) {
                                            $code = $type->code ? " ({$type->code})" : '';

                                            return [$type->id => $type->name.$code];
                                        })
                                        ->toArray();
                                })
                                ->default(2)
                                ->searchable()
                                ->disabled()
                                ->dehydrated(true)
                                ->preload()
                                ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return;
                                    }

                                    if (blank($get('amount'))) {
                                        $set('amount', (float) ($type->minimum_initial_deposit ?? 0));
                                    }
                                })
                                ->required(),

                            TextInput::make('type')
                                ->label('Type')
                                ->default('Deposit')
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('₱')
                                ->minValue(function (callable $get): ?string {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (float) ($type->minimum_initial_deposit ?? 0);

                                    return $min > 0 ? $min : null;
                                })
                                ->rules(function (callable $get) {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return [];
                                    }

                                    $min = (float) ($type->minimum_initial_deposit ?? 0);

                                    return $min > 0 ? ["min:{$min}"] : [];
                                })
                                ->helperText(function (callable $get): ?string {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (float) ($type->minimum_initial_deposit ?? 0);

                                    return $min > 0 ? 'Minimum initial deposit: '.static::money($min) : null;
                                })
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('notes')
                                ->label('Notes')
                                ->placeholder('Optional notes about this transaction')
                                ->maxLength(255),

                            DatePicker::make('transaction_date')
                                ->label('Transaction Date')
                                ->default(now())
                                ->required(),

                            FileUpload::make('proof_of_payment')
                                ->label('Proof of Payment')
                                ->disk('public')
                                ->directory('savings/proof-of-payment')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                ->maxSize(4096)
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data) {
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            SavingsAccountTransaction::create([
                                'profile_id' => $data['profile_id'],
                                'savings_type_id' => $data['savings_type_id'],
                                'type' => $data['type'],
                                'deposit' => $data['amount'],
                                'transaction_date' => $data['transaction_date'],
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Savings Approved')
                                ->success()
                                ->send();
                        }),

                    Action::make('add_time_deposit')
                        ->label('Add Time Deposit')
                        ->icon('heroicon-o-clock')
                        ->color('success')
                        ->form([
                            Select::make('profile_id')
                                ->label('Member')
                                ->options(function ($record) {
                                    return Profile::where('profile_id', $record->profile_id)
                                        ->get()
                                        ->mapWithKeys(function ($profile) {
                                            return [$profile->profile_id => $profile->full_name];
                                        })
                                        ->toArray();
                                })
                                ->default(fn ($record) => $record->profile_id)
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            Select::make('savings_type_id')
                                ->label('Savings Type')
                                ->options(function () {
                                    return SavingsType::where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($type) {
                                            $code = $type->code ? " ({$type->code})" : '';

                                            return [$type->id => $type->name.$code];
                                        })
                                        ->toArray();
                                })
                                ->default(1)
                                ->searchable()
                                ->disabled()
                                ->dehydrated(true)
                                ->preload()
                                ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                    if (! $state) {
                                        return;
                                    }

                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return;
                                    }

                                    if (blank($get('terms'))) {
                                        $set('terms', (int) ($type->minimum_terms ?? 0));
                                    }

                                    if (blank($get('amount'))) {
                                        $set('amount', (float) ($type->minimum_initial_deposit ?? 0));
                                    }
                                })
                                ->required(),

                            TextInput::make('terms')
                                ->label('Term (Months)')
                                ->suffix('months')
                                ->numeric()
                                ->minValue(function (callable $get): ?string {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (int) ($type->minimum_terms ?? 4);

                                    return $min > 0 ? $min : null;
                                })
                                ->rules(function (callable $get) {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return [];
                                    }

                                    $min = (int) ($type->minimum_terms ?? 4);

                                    return $min > 0 ? ["min:{$min}"] : [];
                                })
                                ->helperText(function (callable $get): ?string {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (int) ($type->minimum_terms ?? 4);

                                    return $min > 0 ? "Minimum term: {$min} month(s)." : null;
                                })
                                ->required(),

                            TextInput::make('type')
                                ->label('Type')
                                ->default('Deposit')
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('₱')
                                ->minValue(function (callable $get): ?string {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (float) ($type->minimum_initial_deposit ?? 0);

                                    return $min > 0 ? $min : null;
                                })
                                ->rules(function (callable $get) {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return [];
                                    }

                                    $min = (float) ($type->minimum_initial_deposit ?? 0);

                                    return $min > 0 ? ["min:{$min}"] : [];
                                })
                                ->helperText(function (callable $get): ?string {
                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (float) ($type->minimum_initial_deposit ?? 0);

                                    return $min > 0 ? 'Minimum initial deposit: '.static::money($min) : null;
                                })
                                ->required(),

                            TextInput::make('notes')
                                ->label('Notes')
                                ->placeholder('Optional notes about this transaction')
                                ->maxLength(255),

                            DatePicker::make('transaction_date')
                                ->label('Transaction Date')
                                ->default(now())
                                ->required(),

                            FileUpload::make('proof_of_payment')
                                ->label('Proof of Payment')
                                ->disk('public')
                                ->directory('savings/proof-of-payment')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                ->maxSize(4096)
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data) {
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            SavingsAccountTransaction::create([
                                'profile_id' => $data['profile_id'],
                                'savings_type_id' => $data['savings_type_id'],
                                'deposit' => $data['amount'],
                                'type' => $data['type'],
                                'status' => 'ongoing',
                                'terms' => $data['terms'],
                                'transaction_date' => $data['transaction_date'],
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Time Deposit Added Successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('add_withdrawal')
                        ->label('Add Withdrawal')
                        ->icon('heroicon-o-banknotes')
                        ->color('info')
                        ->form([
                            Select::make('profile_id')
                                ->label('Member')
                                ->options(function ($record) {
                                    return Profile::where('profile_id', $record->profile_id)
                                        ->get()
                                        ->mapWithKeys(function ($profile) {
                                            return [$profile->profile_id => $profile->full_name];
                                        })
                                        ->toArray();
                                })
                                ->default(fn ($record) => $record->profile_id)
                                ->disabled()
                                ->dehydrated(true)
                                ->required()
                                ->live(),

                            Select::make('savings_type_id')
                                ->label('Savings Type')
                                ->options(function () {
                                    return SavingsType::where('is_active', true)
                                        ->get()
                                        ->mapWithKeys(function ($type) {
                                            $code = $type->code ? " ({$type->code})" : '';

                                            return [$type->id => $type->name.$code];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->required()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $set('time_deposit_transaction_id', null);
                                    $set('amount', null);
                                }),

                            Select::make('time_deposit_transaction_id')
                                ->label('Select Time Deposit Transaction')
                                ->options(function (callable $get) {
                                    $profileId = $get('profile_id');
                                    $savingsTypeId = $get('savings_type_id');

                                    if (! $profileId || (int) $savingsTypeId !== 1) {
                                        return [];
                                    }

                                    return SavingsAccountTransaction::where('profile_id', $profileId)
                                        ->where('savings_type_id', 1)
                                        ->where('type', 'Deposit')
                                        ->whereIn('status', ['ongoing', 'completed'])
                                        ->get()
                                        ->mapWithKeys(function ($account) {
                                            return [
                                                $account->id => 'Time Deposit: ₱'.number_format((float) ($account->deposit ?? 0), 2)
                                                    .' | Term: '.($account->terms ?? 'N/A')
                                                    .' | Status: '.ucfirst($account->status ?? 'ongoing')
                                                    .' | Date: '.optional($account->transaction_date)->format('Y-m-d'),
                                            ];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->live()
                                ->visible(fn (callable $get) => (int) $get('savings_type_id') === 1)
                                ->required(fn (callable $get) => (int) $get('savings_type_id') === 1)
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (! $state) {
                                        $set('amount', null);

                                        return;
                                    }

                                    $transaction = SavingsAccountTransaction::find($state);

                                    $set('amount', (float) ($transaction?->deposit ?? 0));
                                }),

                            TextInput::make('type')
                                ->label('Type')
                                ->default('Withdrawal')
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('₱')
                                ->required()
                                ->live()
                                ->readOnly(fn (callable $get) => (int) $get('savings_type_id') === 1)
                                ->maxValue(function (callable $get) {
                                    if ((int) $get('savings_type_id') === 2) {
                                        return static::getRegularSavingsBalance((int) $get('profile_id'));
                                    }

                                    return null;
                                })
                                ->rule(function (callable $get) {
                                    if ((int) $get('savings_type_id') === 2) {
                                        $balance = static::getRegularSavingsBalance((int) $get('profile_id'));

                                        return 'lte:'.$balance;
                                    }

                                    return null;
                                })
                                ->helperText(function (callable $get) {
                                    if ((int) $get('savings_type_id') === 1) {
                                        return 'Amount is auto-filled based on the selected time deposit transaction and cannot be edited.';
                                    }

                                    if ((int) $get('savings_type_id') === 2) {
                                        $balance = static::getRegularSavingsBalance((int) $get('profile_id'));

                                        return 'Available balance: ₱'.number_format($balance, 2);
                                    }

                                    return null;
                                })
                                ->validationMessages([
                                    'lte' => 'The withdrawal amount cannot be greater than the available balance.',
                                ]),

                            TextInput::make('notes')
                                ->label('Notes')
                                ->placeholder('Optional notes about this transaction')
                                ->maxLength(255),

                            FileUpload::make('proof_of_payment')
                                ->label('Proof of Payment')
                                ->disk('public')
                                ->directory('savings/proof-of-payment')
                                ->visibility('public')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                ->maxSize(4096)
                                ->nullable()
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data) {
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $amount = (float) ($data['amount'] ?? 0);
                            $timeDepositTransaction = null;

                            if ((int) $data['savings_type_id'] === 1) {
                                $timeDepositTransaction = SavingsAccountTransaction::find($data['time_deposit_transaction_id'] ?? null);

                                if (! $timeDepositTransaction) {
                                    Notification::make()
                                        ->title('Time deposit transaction not found.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                if (! static::isTimeDepositMatured($timeDepositTransaction)) {
                                    $maturityDate = static::getTimeDepositMaturityDate($timeDepositTransaction);

                                    Notification::make()
                                        ->title('Cannot Withdraw Yet')
                                        ->body('Maturity Date: '.($maturityDate ? $maturityDate->format('Y-m-d') : 'N/A'))
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                if (($timeDepositTransaction->status ?? null) === 'ongoing') {
                                    $timeDepositTransaction->update([
                                        'status' => 'completed',
                                    ]);
                                }

                                if (($timeDepositTransaction->status ?? null) === 'withdrawn') {
                                    Notification::make()
                                        ->title('Already withdrawn')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $amount = (float) ($timeDepositTransaction->deposit ?? 0);
                            }

                            SavingsAccountTransaction::create([
                                'profile_id' => $data['profile_id'],
                                'savings_type_id' => $data['savings_type_id'],
                                'type' => $data['type'],
                                'withdrawal' => $amount,
                                'transaction_date' => now(),
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ]);

                            if ((int) $data['savings_type_id'] === 1 && $timeDepositTransaction) {
                                $timeDepositTransaction->update([
                                    'status' => 'withdrawn',
                                ]);
                            }

                            Notification::make()
                                ->title('Withdrawal saved successfully')
                                ->success()
                                ->send();
                        }),
                ])
                ->visible(fn () => ! auth()->user()?->isMember()),
            ])
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns);
    }
}