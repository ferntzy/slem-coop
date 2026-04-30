<?php

namespace App\Filament\Resources\MemberDetails\Tables;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use App\Models\CoopSetting;
use App\Models\MemberDetail;
use App\Models\Profile;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use App\Models\ShareCapitalTransaction;
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
use Illuminate\Support\Facades\Schema;

class MemberDetailsTable
{
    protected static ?array $transactionalSavingsTypeIds = null;

    protected static ?int $dormancyMonthsThreshold = null;

    protected static array $profileDormancyStatusCache = [];

    protected const MATURITY_ACTION_TRANSFER_TO_SAVINGS = 'transfer_to_savings';

    protected const MATURITY_ACTION_RENEW_TIME_DEPOSIT = 'renew_time_deposit';

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
        return $amount !== null ? 'PHP '.number_format($amount, 2) : '-';
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

    protected static function getDormancyMonthsThreshold(): int
    {
        if (static::$dormancyMonthsThreshold !== null) {
            return static::$dormancyMonthsThreshold;
        }

        static::$dormancyMonthsThreshold = max((int) CoopSetting::get('savings.dormancy_months_threshold', 24), 1);

        return static::$dormancyMonthsThreshold;
    }

    /**
     * @return array<int, string>
     */
    protected static function getTransactionalSavingsTypeIds(): array
    {
        if (static::$transactionalSavingsTypeIds !== null) {
            return static::$transactionalSavingsTypeIds;
        }

        $query = SavingsType::query();

        if (Schema::hasColumn('savings_types', 'is_active')) {
            $query->where('is_active', true);
        }

        $hasDepositAllowed = Schema::hasColumn('savings_types', 'deposit_allowed');
        $hasWithdrawalAllowed = Schema::hasColumn('savings_types', 'withdrawal_allowed');

        if ($hasDepositAllowed && $hasWithdrawalAllowed) {
            $query
                ->where('deposit_allowed', true)
                ->where('withdrawal_allowed', true);
        }

        static::$transactionalSavingsTypeIds = $query
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        return static::$transactionalSavingsTypeIds;
    }

    protected static function getSavingsDormancyStatus(int $profileId): string
    {
        if ($profileId <= 0) {
            return 'No Savings';
        }

        if (array_key_exists($profileId, static::$profileDormancyStatusCache)) {
            return static::$profileDormancyStatusCache[$profileId];
        }

        $transactionalSavingsTypeIds = static::getTransactionalSavingsTypeIds();

        if ($transactionalSavingsTypeIds === []) {
            static::$profileDormancyStatusCache[$profileId] = 'No Savings';

            return static::$profileDormancyStatusCache[$profileId];
        }

        $positiveBalanceSavingsTypeIds = SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->whereIn('savings_type_id', $transactionalSavingsTypeIds)
            ->selectRaw('savings_type_id, SUM(COALESCE(deposit, 0) - COALESCE(withdrawal, 0)) as balance')
            ->groupBy('savings_type_id')
            ->havingRaw('SUM(COALESCE(deposit, 0) - COALESCE(withdrawal, 0)) > 0')
            ->pluck('savings_type_id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        if ($positiveBalanceSavingsTypeIds === []) {
            static::$profileDormancyStatusCache[$profileId] = 'No Savings';

            return static::$profileDormancyStatusCache[$profileId];
        }

        $lastCustomerInitiatedTransactionDate = SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->whereIn('savings_type_id', $positiveBalanceSavingsTypeIds)
            ->where(function ($query): void {
                $query->whereIn('direction', ['deposit', 'withdrawal', 'transfer'])
                    ->orWhere(function ($legacyQuery): void {
                        $legacyQuery
                            ->whereNull('direction')
                            ->whereRaw('LOWER(type) in (?, ?, ?)', ['deposit', 'withdrawal', 'transfer']);
                    });
            })
            ->selectRaw('MAX(GREATEST(created_at, COALESCE(transaction_date, created_at))) as last_customer_transaction_date')
            ->value('last_customer_transaction_date');

        if (! $lastCustomerInitiatedTransactionDate) {
            $lastCustomerInitiatedTransactionDate = SavingsAccountTransaction::query()
                ->where('profile_id', $profileId)
                ->whereIn('savings_type_id', $positiveBalanceSavingsTypeIds)
                ->selectRaw('MAX(GREATEST(created_at, COALESCE(transaction_date, created_at))) as last_transaction_date')
                ->value('last_transaction_date');
        }

        if (! $lastCustomerInitiatedTransactionDate) {
            static::$profileDormancyStatusCache[$profileId] = 'Active';

            return static::$profileDormancyStatusCache[$profileId];
        }

        $cutoffDate = now()->copy()->subMonths(static::getDormancyMonthsThreshold())->startOfDay();

        static::$profileDormancyStatusCache[$profileId] = Carbon::parse($lastCustomerInitiatedTransactionDate)
            ->startOfDay()
            ->lessThanOrEqualTo($cutoffDate)
            ? 'Dormant'
            : 'Active';

        return static::$profileDormancyStatusCache[$profileId];
    }

    protected static function isTimeDepositDecisionWindowOpen(?SavingsAccountTransaction $transaction): bool
    {
        $maturityDate = static::getTimeDepositMaturityDate($transaction);

        if (! $maturityDate || ($transaction?->status !== 'ongoing')) {
            return false;
        }

        return now()->greaterThanOrEqualTo($maturityDate->copy()->subWeek());
    }

    protected static function getMaturityActionLabel(?string $maturityAction): string
    {
        return match ($maturityAction) {
            static::MATURITY_ACTION_RENEW_TIME_DEPOSIT => 'Re-Time Deposit',
            static::MATURITY_ACTION_TRANSFER_TO_SAVINGS => 'Transfer to Regular Savings',
            default => 'Auto-transfer to Regular Savings',
        };
    }

    protected static function markMemberActive(int $profileId): void
    {
        MemberDetail::query()
            ->where('profile_id', $profileId)
            ->update(['status' => 'Active']);

        unset(static::$profileDormancyStatusCache[$profileId]);
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
                    ->label('Status')
                    ->state(function ($record): string {
                        $memberStatus = (string) ($record->status ?? 'Unknown');
                        $dormancyStatus = static::getSavingsDormancyStatus((int) $record->profile_id);

                        return $dormancyStatus === 'Dormant'
                            ? 'Dormant'
                            : $memberStatus;
                    })
                    ->badge()
                    ->color(function (string $state): string {
                        if (str_contains($state, 'Dormant')) {
                            return 'danger';
                        }

                        return match (strtolower($state)) {
                            'active' => 'success',
                            'delinquent' => 'warning',
                            'inactive' => 'gray',
                            default => 'gray',
                        };
                    })
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

                    Action::make('add_deposit')
                        ->label('Deposit')
                        ->icon('heroicon-o-arrow-down-circle')
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
                                        ->whereIn('id', [1, 2])
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
                                ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                    $set('terms', null);

                                    if (! $state) {
                                        $set('amount', null);

                                        return;
                                    }

                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return;
                                    }

                                    if ((int) $state === 1) {
                                        $set('terms', (int) ($type->minimum_terms ?? 4));
                                    }

                                    $set('amount', (float) ($type->minimum_initial_deposit ?? 0));
                                })
                                ->required(),

                            TextInput::make('terms')
                                ->label('Term (Months)')
                                ->suffix('months')
                                ->numeric()
                                ->minValue(function (callable $get): ?string {
                                    if ((int) $get('savings_type_id') !== 1) {
                                        return null;
                                    }

                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (int) ($type->minimum_terms ?? 4);

                                    return $min > 0 ? $min : null;
                                })
                                ->rules(function (callable $get): array {
                                    if ((int) $get('savings_type_id') !== 1) {
                                        return [];
                                    }

                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return [];
                                    }

                                    $min = (int) ($type->minimum_terms ?? 4);

                                    return $min > 0 ? ["min:{$min}"] : [];
                                })
                                ->helperText(function (callable $get): ?string {
                                    if ((int) $get('savings_type_id') !== 1) {
                                        return null;
                                    }

                                    $type = static::getSavingsType($get);

                                    if (! $type) {
                                        return null;
                                    }

                                    $min = (int) ($type->minimum_terms ?? 4);

                                    return $min > 0 ? "Minimum term: {$min} month(s)." : null;
                                })
                                ->visible(fn (callable $get): bool => (int) $get('savings_type_id') === 1)
                                ->dehydrated(fn (callable $get): bool => (int) $get('savings_type_id') === 1)
                                ->required(fn (callable $get): bool => (int) $get('savings_type_id') === 1),

                            TextInput::make('type')
                                ->label('Type')
                                ->default('Deposit')
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('PHP')
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

                            $transactionData = [
                                'profile_id' => $data['profile_id'],
                                'savings_type_id' => $data['savings_type_id'],
                                'type' => $data['type'],
                                'direction' => 'deposit',
                                'deposit' => $data['amount'],
                                'amount' => $data['amount'],
                                'transaction_date' => $data['transaction_date'],
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ];

                            if ((int) $data['savings_type_id'] === 1) {
                                $transactionData['status'] = 'ongoing';
                                $transactionData['terms'] = $data['terms'];
                            }

                            SavingsAccountTransaction::create($transactionData);

                            static::markMemberActive((int) $data['profile_id']);
                            static::$transactionalSavingsTypeIds = null;

                            Notification::make()
                                ->title((int) $data['savings_type_id'] === 1
                                    ? 'Time Deposit Added Successfully'
                                    : 'Savings Approved')
                                ->success()
                                ->send();
                        })
                        ->after(fn ($livewire) => $livewire->dispatch('$refresh')),

                    Action::make('add_share_capital')
                        ->label('Add Share Capital')
                        ->icon('heroicon-o-currency-dollar')
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

                            TextInput::make('type')
                                ->label('Type')
                                ->default('Deposit')
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('PHP')
                                ->minValue(0.01)
                                ->required(),

                            DatePicker::make('transaction_date')
                                ->label('Transaction Date')
                                ->default(now())
                                ->required(),

                            TextInput::make('reference_no')
                                ->label('Reference No.')
                                ->maxLength(50),

                            TextInput::make('notes')
                                ->label('Notes')
                                ->placeholder('Optional notes about this transaction')
                                ->maxLength(255),
                        ])
                        ->action(function ($record, array $data) {
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            ShareCapitalTransaction::create([
                                'profile_id' => $data['profile_id'],
                                'amount' => $data['amount'],
                                'direction' => 'credit',
                                'type' => 'deposit',
                                'transaction_date' => $data['transaction_date'],
                                'reference_no' => $data['reference_no'] ?? null,
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Share capital added successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('add_withdrawal')
                        ->label('Withdraw')
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
                                        ->where('id', 2)
                                        ->get()
                                        ->mapWithKeys(function ($type) {
                                            $code = $type->code ? " ({$type->code})" : '';

                                            return [$type->id => $type->name.$code];
                                        })
                                        ->toArray();
                                })
                                ->default(2)
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disabled()
                                ->dehydrated(true),

                            TextInput::make('type')
                                ->label('Type')
                                ->default('Withdrawal')
                                ->disabled()
                                ->dehydrated(true)
                                ->required(),

                            TextInput::make('amount')
                                ->label('Amount')
                                ->numeric()
                                ->prefix('PHP')
                                ->required()
                                ->live()
                                ->maxValue(function (callable $get) {
                                    return static::getRegularSavingsBalance((int) $get('profile_id'));
                                })
                                ->rule(function (callable $get) {
                                    $balance = static::getRegularSavingsBalance((int) $get('profile_id'));

                                    return 'lte:'.$balance;
                                })
                                ->helperText(function (callable $get) {
                                    $balance = static::getRegularSavingsBalance((int) $get('profile_id'));

                                    return 'Available balance: PHP '.number_format($balance, 2);
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

                            SavingsAccountTransaction::create([
                                'profile_id' => $data['profile_id'],
                                'savings_type_id' => $data['savings_type_id'],
                                'type' => $data['type'],
                                'direction' => 'withdrawal',
                                'withdrawal' => (float) ($data['amount'] ?? 0),
                                'transaction_date' => now(),
                                'notes' => $data['notes'] ?? null,
                                'posted_by_user_id' => auth()->id(),
                            ]);

                            static::markMemberActive((int) $data['profile_id']);
                            static::$transactionalSavingsTypeIds = null;

                            Notification::make()
                                ->title('Withdrawal saved successfully')
                                ->success()
                                ->send();
                        })
                        ->after(fn ($livewire) => $livewire->dispatch('$refresh')),
                ])
                    ->visible(fn () => ! auth()->user()?->isMember()),
            ])
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns);
    }
}
