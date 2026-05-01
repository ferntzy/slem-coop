<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use App\Filament\Widgets\RegularSavingsTransactionsTable;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Str;

class MemberDetailInfolist
{
    /**
     * @var array<string, ?SavingsType>
     */
    protected static array $savingsTypeCache = [];

    /**
     * @var array<string, Collection<int, SavingsAccountTransaction>>
     */
    protected static array $transactionsCache = [];

    /**
     * @var array<int, array<int, array<string, mixed>>>
     */
    protected static array $timeDepositDisplayCache = [];

    /**
     * @var array<int, string>|null
     */
    protected static ?array $transactionalSavingsTypeIds = null;

    protected static function getRegularSavingsType(): ?SavingsType
    {
        return static::$savingsTypeCache['regular'] ??= SavingsType::query()
            ->where('name', 'Regular Savings')
            ->orWhere('code', 'SA 02')
            ->first();
    }

    protected static function getTimeDepositType(): ?SavingsType
    {
        return static::$savingsTypeCache['time_deposit'] ??= SavingsType::query()
            ->where('name', 'Time Deposit')
            ->orWhere('code', 'SA 01')
            ->first();
    }

    protected static function clearTransactionCaches(int $profileId): void
    {
        if ($profileId <= 0) {
            return;
        }

        unset(static::$timeDepositDisplayCache[$profileId]);

        $regularSavingsType = static::getRegularSavingsType();
        $timeDepositType = static::getTimeDepositType();

        if ($regularSavingsType) {
            unset(static::$transactionsCache[$profileId.':'.$regularSavingsType->getKey()]);
        }

        if ($timeDepositType) {
            unset(static::$transactionsCache[$profileId.':'.$timeDepositType->getKey()]);
        }

        unset(static::$transactionsCache[$profileId.':regular-transactional']);
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

        if (SchemaFacade::hasColumn('savings_types', 'is_active')) {
            $query->where('is_active', true);
        }

        $hasDepositAllowed = SchemaFacade::hasColumn('savings_types', 'deposit_allowed');
        $hasWithdrawalAllowed = SchemaFacade::hasColumn('savings_types', 'withdrawal_allowed');

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

    /**
     * @return Collection<int, SavingsAccountTransaction>
     */
    protected static function getRegularSavingsTransactions(int $profileId): Collection
    {
        $transactionalSavingsTypeIds = static::getTransactionalSavingsTypeIds();

        if (! $profileId || $transactionalSavingsTypeIds === []) {
            return collect();
        }

        $cacheKey = $profileId.':regular-transactional';

        return static::$transactionsCache[$cacheKey] ??= SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->whereIn('savings_type_id', $transactionalSavingsTypeIds)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return Collection<int, SavingsAccountTransaction>
     */
    protected static function getSavingsTransactions(int $profileId, ?SavingsType $savingsType): Collection
    {
        if (! $profileId || ! $savingsType) {
            return collect();
        }

        $cacheKey = $profileId.':'.$savingsType->getKey();

        return static::$transactionsCache[$cacheKey] ??= SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', (string) $savingsType->getKey())
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    protected static function getBalance(Collection $transactions): float
    {
        return round($transactions->sum(function (SavingsAccountTransaction $transaction): float {
            return static::getTransactionDepositAmount($transaction) - static::getTransactionWithdrawalAmount($transaction);
        }), 2);
    }

    protected static function getTransactionAmount(SavingsAccountTransaction $transaction): float
    {
        $depositAmount = static::getTransactionDepositAmount($transaction);

        if ($depositAmount > 0) {
            return $depositAmount;
        }

        return static::getTransactionWithdrawalAmount($transaction);
    }

    protected static function getTransactionDepositAmount(SavingsAccountTransaction $transaction): float
    {
        $depositAmount = (float) ($transaction->deposit ?? 0);

        if ($depositAmount > 0) {
            return round($depositAmount, 2);
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['deposit', 'credit'], true) || in_array($direction, ['credit', 'inflow'], true)) {
            return round((float) ($transaction->amount ?? 0), 2);
        }

        return 0.0;
    }

    protected static function getTransactionWithdrawalAmount(SavingsAccountTransaction $transaction): float
    {
        $withdrawalAmount = (float) ($transaction->withdrawal ?? 0);

        if ($withdrawalAmount > 0) {
            return round($withdrawalAmount, 2);
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['withdrawal', 'debit'], true) || in_array($direction, ['debit', 'outflow'], true)) {
            return round((float) ($transaction->amount ?? 0), 2);
        }

        return 0.0;
    }

    /**
     * @return array<int, mixed>
     */
    protected static function getLatestTransactions(Collection $transactions, int $limit = 3): array
    {
        return $transactions
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<int, mixed>
     */
    protected static function getOlderTransactions(Collection $transactions, int $offset = 3): array
    {
        return $transactions
            ->slice($offset)
            ->values()
            ->all();
    }

    protected static function getTimeDepositMaturityDate(SavingsAccountTransaction $transaction): ?Carbon
    {
        if (! $transaction->transaction_date || ! $transaction->terms) {
            return null;
        }

        return $transaction->transaction_date->copy()->addMonths((int) $transaction->terms);
    }

    protected static function isTimeDepositDecisionWindowOpen(SavingsAccountTransaction $transaction): bool
    {
        $maturityDate = static::getTimeDepositMaturityDate($transaction);

        if (! $maturityDate || ($transaction->status !== 'ongoing')) {
            return false;
        }

        return now()->greaterThanOrEqualTo($maturityDate->copy()->subWeek());
    }

    protected static function getMaturityActionLabel(?string $maturityAction): string
    {
        return match ($maturityAction) {
            'renew_time_deposit' => 'Re-Time Deposit',
            'transfer_to_savings' => 'Transfer to Regular Savings',
            default => 'Auto-transfer to Regular Savings',
        };
    }

    /**
     * @return array<int, string>
     */
    protected static function getEligibleTimeDepositOptions(int $profileId): array
    {
        $timeDepositType = static::getTimeDepositType();

        if (! $timeDepositType || $profileId <= 0) {
            return [];
        }

        return SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', (string) $timeDepositType->getKey())
            ->where('type', 'Deposit')
            ->where('status', 'ongoing')
            ->whereNull('maturity_action')
            ->get()
            ->filter(fn (SavingsAccountTransaction $transaction): bool => static::isTimeDepositDecisionWindowOpen($transaction))
            ->mapWithKeys(function (SavingsAccountTransaction $transaction): array {
                $maturityDate = static::getTimeDepositMaturityDate($transaction);

                return [
                    $transaction->id => 'Time Deposit: PHP '.number_format((float) ($transaction->deposit ?? 0), 2)
                        .' | Term: '.($transaction->terms ?? 'N/A').' month(s)'
                        .' | Maturity: '.($maturityDate?->format('Y-m-d') ?? 'N/A')
                        .' | Current Option: '.static::getMaturityActionLabel($transaction->maturity_action),
                ];
            })
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function getEligibleTimeDepositDisplayTransactions(int $profileId): array
    {
        $timeDepositType = static::getTimeDepositType();

        if (! $timeDepositType || $profileId <= 0) {
            return [];
        }

        return SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', (string) $timeDepositType->getKey())
            ->where('type', 'Deposit')
            ->where('status', 'ongoing')
            ->whereNull('maturity_action')
            ->get()
            ->filter(fn (SavingsAccountTransaction $transaction): bool => static::isTimeDepositDecisionWindowOpen($transaction))
            ->map(function (SavingsAccountTransaction $transaction): array {
                $maturityDate = static::getTimeDepositMaturityDate($transaction);

                return [
                    'id' => (int) $transaction->id,
                    'amount' => round((float) ($transaction->deposit ?? 0), 2),
                    'terms' => $transaction->terms,
                    'status' => $transaction->status,
                    'maturity_action' => $transaction->maturity_action,
                    'transaction_date' => $transaction->transaction_date,
                    'maturity_date' => $maturityDate,
                ];
            })
            ->sortByDesc(fn (array $transaction): string => sprintf(
                '%s-%010d',
                optional($transaction['transaction_date'])->format('Y-m-d H:i:s.u') ?? '',
                $transaction['id']
            ))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function getTimeDepositDisplayTransactions(int $profileId): array
    {
        if (isset(static::$timeDepositDisplayCache[$profileId])) {
            return static::$timeDepositDisplayCache[$profileId];
        }

        $timeDepositTransactions = static::getSavingsTransactions($profileId, static::getTimeDepositType())
            ->where('type', 'Deposit')
            ->values();

        if ($timeDepositTransactions->isEmpty()) {
            return static::$timeDepositDisplayCache[$profileId] = [];
        }

        $transferRows = static::getSavingsTransactions($profileId, static::getRegularSavingsType())
            ->where('type', 'Deposit')
            ->filter(fn (SavingsAccountTransaction $transaction): bool => str_contains(
                (string) $transaction->notes,
                'matured time deposit #'
            ))
            ->mapWithKeys(function (SavingsAccountTransaction $transaction): array {
                preg_match('/matured time deposit #(\d+)/', (string) $transaction->notes, $matches);

                if (! isset($matches[1])) {
                    return [];
                }

                return [
                    (int) $matches[1] => [
                        'transferred_amount' => $transaction->deposit,
                        'transfer_date' => $transaction->transaction_date,
                    ],
                ];
            });

        return static::$timeDepositDisplayCache[$profileId] = $timeDepositTransactions
            ->map(function (SavingsAccountTransaction $transaction) use ($transferRows): array {
                $transferRow = $transferRows->get((int) $transaction->id);
                $maturityDate = static::getTimeDepositMaturityDate($transaction);

                return [
                    'id' => (int) $transaction->id,
                    'amount' => round((float) ($transaction->deposit ?? 0), 2),
                    'status' => $transaction->status,
                    'maturity_action' => $transaction->maturity_action,
                    'terms' => $transaction->terms,
                    'transaction_date' => $transaction->transaction_date,
                    'maturity_date' => $maturityDate,
                    'transferred_amount' => $transferRow['transferred_amount'] ?? null,
                    'transfer_date' => $transferRow['transfer_date'] ?? null,
                    'notes' => $transaction->notes,
                ];
            })
            ->sortByDesc(fn (array $transaction): string => sprintf(
                '%s-%010d',
                optional($transaction['transaction_date'])->format('Y-m-d H:i:s.u') ?? '',
                $transaction['id']
            ))
            ->values()
            ->all();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Personal Details')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make('Member Information')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('profile.mobile_number')
                                            ->label('Mobile Number')
                                            ->icon('heroicon-o-phone'),

                                        TextEntry::make('profile.email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable(),

                                        TextEntry::make('member_no')
                                            ->label('Member Number')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('occupation')
                                            ->label('Occupation'),

                                        TextEntry::make('employer_name')
                                            ->label('Employer'),

                                        TextEntry::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('membership_Status')
                                            ->label('Membership Status')
                                            ->getStateUsing(fn ($record) => $record->membershipStatus())
                                            ->badge()
                                            ->color(fn ($state) => $state === 'Active' ? 'success' : 'warning'),
                                    ])
                                    ->columns(3),

                                Section::make('Spouse Information')
                                    ->schema([
                                        TextEntry::make('spouse.full_name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->color('success')
                                            ->placeholder('-'),

                                        TextEntry::make('spouse.birthdate')
                                            ->label('Birthdate')
                                            ->placeholder('-'),

                                        TextEntry::make('spouse.occupation')
                                            ->label('Occupation')
                                            ->placeholder('-'),

                                        TextEntry::make('spouse.employer_name')
                                            ->label('Employer')
                                            ->placeholder('-'),

                                        TextEntry::make('spouse.source_of_income')
                                            ->label('Source of Income')
                                            ->placeholder('-'),

                                        TextEntry::make('spouse.monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(3),

                                Section::make('Co-Makers')
                                    ->schema([
                                        RepeatableEntry::make('coMakers')
                                            ->label('Co-Makers')
                                            ->schema([
                                                TextEntry::make('full_name')
                                                    ->label('Full Name')
                                                    ->weight('bold')
                                                    ->color('info'),
                                                TextEntry::make('relationship')
                                                    ->label('Relationship')
                                                    ->badge(),
                                                TextEntry::make('contact_number')
                                                    ->label('Contact Number')
                                                    ->icon('heroicon-o-phone'),
                                                TextEntry::make('address')
                                                    ->label('Address'),
                                                TextEntry::make('occupation')
                                                    ->label('Occupation'),
                                                TextEntry::make('employer_name')
                                                    ->label('Employer'),
                                                TextEntry::make('monthly_income')
                                                    ->label('Monthly Income')
                                                    ->money('PHP')
                                                    ->weight('bold')
                                                    ->color('success'),
                                            ])
                                            ->columns(3)
                                            ->contained(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Employment & Identification')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Employment Information')
                                    ->schema([
                                        TextEntry::make('occupation')
                                            ->label('Occupation')
                                            ->weight('bold'),

                                        TextEntry::make('employer_name')
                                            ->label('Employer / Business Name')
                                            ->weight('bold')
                                            ->color('info'),

                                        TextEntry::make('source_of_income')
                                            ->label('Source of Income'),

                                        TextEntry::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('monthly_income_range')
                                            ->label('Monthly Income Range')
                                            ->badge()
                                            ->color('secondary'),
                                    ])
                                    ->columns(2),

                                Section::make('Identification')
                                    ->schema([
                                        TextEntry::make('id_type')
                                            ->label('ID Type')
                                            ->badge(),

                                        TextEntry::make('id_number')
                                            ->label('ID Number')
                                            ->copyable(),
                                    ])
                                    ->columns(2),

                                Section::make('Emergency Contact')
                                    ->schema([
                                        TextEntry::make('emergency_full_name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('emergency_phone')
                                            ->label('Phone Number')
                                            ->icon('heroicon-o-phone'),

                                        TextEntry::make('emergency_relationship')
                                            ->label('Relationship')
                                            ->badge(),
                                    ])
                                    ->columns(3),

                                Section::make('Household Information')
                                    ->schema([
                                        TextEntry::make('dependents_count')
                                            ->label('Number of Dependents')
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('children_in_school_count')
                                            ->label('Children in School')
                                            ->badge()
                                            ->color('warning'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Shared Capital Transactions')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextEntry::make('share_capital_balance')
                                    ->label('Share Capital Balance')
                                    ->money('PHP')
                                    ->weight('bold')
                                    ->color('success')
                                    ->size('lg'),

                                RepeatableEntry::make('sharedCapitalTransactions')
                                    ->label('Share Capital Transactions')
                                    ->schema([
                                        TextEntry::make('transaction_date')
                                            ->label('Date')
                                            ->date('F j, Y'),
                                        TextEntry::make('amount')
                                            ->label('Amount')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),
                                        TextEntry::make('direction')
                                            ->label('Direction')
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'credit' => 'success',
                                                'debit' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('type')
                                            ->label('Type')
                                            ->badge(),
                                        TextEntry::make('reference_no')
                                            ->label('Reference No.')
                                            ->placeholder('-'),
                                        TextEntry::make('notes')
                                            ->label('Notes')
                                            ->placeholder('-'),
                                        TextEntry::make('postedBy.name')
                                            ->label('Posted By')
                                            ->placeholder('-'),
                                    ])
                                    ->columns(3)
                                    ->contained(),
                            ]),

                        Tab::make('Savings')
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                Section::make('Regular Savings Overview')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Member Name'),
                                        TextEntry::make('regular_savings_type')
                                            ->label('Savings Type')
                                            ->state(function ($record): string {
                                                $type = static::getRegularSavingsType();

                                                return $type ? (string) $type->name : '-';
                                            }),

                                        TextEntry::make('regular_savings_interest_rate')
                                            ->label('Interest Rate')
                                            ->state(function ($record): float {
                                                $type = static::getRegularSavingsType();

                                                return $type ? round((float) ($type->interest_rate ?? 0), 2) : 0.0;
                                            })
                                            ->formatStateUsing(fn ($state) => $state !== null ? ($state.'%') : '-'),

                                        TextEntry::make('regular_savings_balance')
                                            ->label('Current Balance')
                                            ->state(function ($record): float {
                                                $transactions = static::getRegularSavingsTransactions((int) $record->profile_id);

                                                return static::getBalance($transactions);
                                            })
                                            ->money('PHP'),
                                    ])
                                    ->columns(2),

                                Livewire::make(RegularSavingsTransactionsTable::class)
                                    ->columnSpanFull(),

                                Section::make('Time Deposit Overview')
                                    ->schema([
                                        TextEntry::make('time_deposit_principal_total')
                                            ->label('Principal Total')
                                            ->state(function ($record): float {
                                                $transactions = static::getSavingsTransactions(
                                                    (int) $record->profile_id,
                                                    static::getTimeDepositType()
                                                )->where('type', 'Deposit');

                                                return round((float) $transactions->sum('deposit'), 2);
                                            })
                                            ->money('PHP'),
                                        TextEntry::make('time_deposit_interest')
                                            ->label('Interest Rate')
                                            ->state(function ($record): float {
                                                $type = static::getTimeDepositType();

                                                return $type ? round((float) ($type->interest_rate ?? 0), 2) : 0.0;
                                            })
                                            ->formatStateUsing(fn ($state) => $state !== null ? ($state.'%') : '-'),

                                        TextEntry::make('time_deposit_active_count')
                                            ->label('Active Deposits')
                                            ->state(function ($record): int {
                                                return static::getSavingsTransactions(
                                                    (int) $record->profile_id,
                                                    static::getTimeDepositType()
                                                )
                                                    ->where('type', 'Deposit')
                                                    ->where('status', 'ongoing')
                                                    ->count();
                                            }),

                                        TextEntry::make('time_deposit_completed_count')
                                            ->label('Completed Deposits')
                                            ->state(function ($record): int {
                                                return static::getSavingsTransactions(
                                                    (int) $record->profile_id,
                                                    static::getTimeDepositType()
                                                )
                                                    ->where('type', 'Deposit')
                                                    ->where('status', 'completed')
                                                    ->count();
                                            }),
                                    ])
                                    ->columns(3),

                                Section::make('Time Deposit Transactions')
                                    ->extraAttributes([
                                        'class' => 'max-w-full overflow-hidden',
                                        'style' => 'max-width: 100%; overflow: hidden;',
                                    ])
                                    ->schema([
                                        Section::make('Eligible for Maturity Option')
                                            ->description('Only time deposits within 7 days before maturity are listed here. If no action is taken, the system will automatically transfer the amount to Regular Savings on the maturity date.')
                                            ->schema([
                                                RepeatableEntry::make('eligible_time_deposit_transactions')
                                                    ->label('Eligible Time Deposits')
                                                    ->extraAttributes(['class' => 'min-w-[72rem]'])
                                                    ->state(function ($record): array {
                                                        return static::getEligibleTimeDepositDisplayTransactions((int) $record->profile_id);
                                                    })
                                                    ->schema([
                                                        TextEntry::make('amount')
                                                            ->label('Amount')
                                                            ->money('PHP'),

                                                        TextEntry::make('terms')
                                                            ->label('Term')
                                                            ->formatStateUsing(fn ($state) => $state ? $state.' month(s)' : '-'),

                                                        TextEntry::make('transaction_date')
                                                            ->label('Deposit Date')
                                                            ->dateTime('M d, Y h:i A'),

                                                        TextEntry::make('maturity_date')
                                                            ->label('Maturity Date')
                                                            ->date('M d, Y')
                                                            ->placeholder('-'),

                                                        TextEntry::make('id')
                                                            ->label('Action')
                                                            ->formatStateUsing(fn () => '')
                                                            ->afterContent(fn ($state): Action => Action::make('re_time_deposit_'.$state)
                                                                ->label('Re-Time Deposit')
                                                                ->icon('heroicon-o-arrow-path-rounded-square')
                                                                ->button()
                                                                ->requiresConfirmation()
                                                                ->visible(fn (): bool => ! auth()->user()?->isMember())
                                                                ->action(function () use ($state): void {
                                                                    if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                                                        Notification::make()
                                                                            ->title('Unauthorized')
                                                                            ->danger()
                                                                            ->send();

                                                                        return;
                                                                    }

                                                                    $timeDepositType = static::getTimeDepositType();

                                                                    $timeDepositTransaction = SavingsAccountTransaction::query()
                                                                        ->where('id', $state)
                                                                        ->where('savings_type_id', (string) ($timeDepositType?->getKey() ?? ''))
                                                                        ->where('type', 'Deposit')
                                                                        ->where('status', 'ongoing')
                                                                        ->whereNull('maturity_action')
                                                                        ->first();

                                                                    if (! $timeDepositTransaction) {
                                                                        Notification::make()
                                                                            ->title('Time deposit transaction not found.')
                                                                            ->danger()
                                                                            ->send();

                                                                        return;
                                                                    }

                                                                    if (! static::isTimeDepositDecisionWindowOpen($timeDepositTransaction)) {
                                                                        $maturityDate = static::getTimeDepositMaturityDate($timeDepositTransaction);

                                                                        Notification::make()
                                                                            ->title('Option not available yet')
                                                                            ->body('This option can only be set within 7 days before maturity. Maturity date: '.($maturityDate?->format('Y-m-d') ?? 'N/A'))
                                                                            ->danger()
                                                                            ->send();

                                                                        return;
                                                                    }

                                                                    if (filled($timeDepositTransaction->maturity_action)) {
                                                                        Notification::make()
                                                                            ->title('Maturity option already selected')
                                                                            ->body('Current option: '.static::getMaturityActionLabel($timeDepositTransaction->maturity_action))
                                                                            ->warning()
                                                                            ->send();

                                                                        return;
                                                                    }

                                                                    $timeDepositTransaction->update([
                                                                        'maturity_action' => 'renew_time_deposit',
                                                                        'maturity_action_selected_at' => now(),
                                                                    ]);

                                                                    static::clearTransactionCaches((int) $timeDepositTransaction->profile_id);

                                                                    Notification::make()
                                                                        ->title('Maturity option saved successfully')
                                                                        ->body('Selected option: Re-Time Deposit')
                                                                        ->success()
                                                                        ->send();
                                                                })),
                                                    ])
                                                    ->columns(5)
                                                    ->contained()
                                                    ->columnSpanFull(),
                                            ])
                                            ->visible(function ($record): bool {
                                                return static::getEligibleTimeDepositDisplayTransactions((int) $record->profile_id) !== [];
                                            }),

                                        RepeatableEntry::make('time_deposit_transactions')
                                            ->label('Latest 3 Transactions')
                                            ->extraAttributes([
                                                'class' => 'w-full max-w-full overflow-x-auto',
                                                'style' => 'width: 100%; max-width: 100%; overflow-x: auto;',
                                            ])
                                            ->state(function ($record): array {
                                                $transactions = collect(
                                                    static::getTimeDepositDisplayTransactions((int) $record->profile_id)
                                                );

                                                return static::getLatestTransactions($transactions);
                                            })
                                            ->schema([
                                                TextEntry::make('amount')
                                                    ->label('Amount')
                                                    ->money('PHP'),

                                                TextEntry::make('terms')
                                                    ->label('Term')
                                                    ->formatStateUsing(fn ($state) => $state ? $state.' month(s)' : '-'),

                                                TextEntry::make('status')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state) {
                                                        'ongoing' => 'warning',
                                                        'completed' => 'success',
                                                        'withdrawn' => 'gray',
                                                        default => 'gray',
                                                    }),

                                                TextEntry::make('maturity_action')
                                                    ->label('Maturity Option')
                                                    ->formatStateUsing(fn ($state) => match ($state) {
                                                        'renew_time_deposit' => 'Re-Time Deposit',
                                                        'transfer_to_savings' => 'Transfer to Regular Savings',
                                                        default => 'Auto-transfer to Regular Savings',
                                                    }),

                                                TextEntry::make('transaction_date')
                                                    ->label('Deposit Date')
                                                    ->dateTime('M d, Y h:i A'),

                                                TextEntry::make('maturity_date')
                                                    ->label('Maturity Date')
                                                    ->date('M d, Y')
                                                    ->placeholder('-'),

                                                TextEntry::make('transferred_amount')
                                                    ->label('Transferred to Regular Savings')
                                                    ->money('PHP')
                                                    ->placeholder('Not transferred yet'),

                                                TextEntry::make('transfer_date')
                                                    ->label('Transfer Date')
                                                    ->dateTime('M d, Y h:i A')
                                                    ->placeholder('-'),

                                                TextEntry::make('notes')
                                                    ->label('Notes')
                                                    ->placeholder('-'),
                                            ])
                                            ->table([
                                                TableColumn::make('Amount')->width('10rem'),
                                                TableColumn::make('Term')->width('8rem'),
                                                TableColumn::make('Status')->width('9rem'),
                                                TableColumn::make('Maturity Option')->width('14rem'),
                                                TableColumn::make('Deposit Date')->width('12rem'),
                                                TableColumn::make('Maturity Date')->width('10rem'),
                                                TableColumn::make('Transferred to Regular Savings')->width('14rem'),
                                                TableColumn::make('Transfer Date')->width('12rem'),
                                                TableColumn::make('Notes')->width('18rem'),
                                            ])
                                            ->contained()
                                            ->columnSpanFull(),

                                        Section::make('More Time Deposit Transactions')
                                            ->schema([
                                                RepeatableEntry::make('older_time_deposit_transactions')
                                                    ->label('Older Transactions')
                                                    ->extraAttributes([
                                                        'class' => 'w-full max-w-full overflow-x-auto',
                                                        'style' => 'width: 100%; max-width: 100%; overflow-x: auto;',
                                                    ])
                                                    ->state(function ($record): array {
                                                        $transactions = collect(
                                                            static::getTimeDepositDisplayTransactions((int) $record->profile_id)
                                                        );

                                                        return static::getOlderTransactions($transactions);
                                                    })
                                                    ->schema([
                                                        TextEntry::make('amount')
                                                            ->label('Amount')
                                                            ->money('PHP'),

                                                        TextEntry::make('terms')
                                                            ->label('Term')
                                                            ->formatStateUsing(fn ($state) => $state ? $state.' month(s)' : '-'),

                                                        TextEntry::make('status')
                                                            ->badge()
                                                            ->color(fn ($state) => match ($state) {
                                                                'ongoing' => 'warning',
                                                                'completed' => 'success',
                                                                'withdrawn' => 'gray',
                                                                default => 'gray',
                                                            }),

                                                        TextEntry::make('maturity_action')
                                                            ->label('Maturity Option')
                                                            ->formatStateUsing(fn ($state) => match ($state) {
                                                                'renew_time_deposit' => 'Re-Time Deposit',
                                                                'transfer_to_savings' => 'Transfer to Regular Savings',
                                                                default => 'Auto-transfer to Regular Savings',
                                                            }),

                                                        TextEntry::make('transaction_date')
                                                            ->label('Deposit Date')
                                                            ->dateTime('M d, Y h:i A'),

                                                        TextEntry::make('maturity_date')
                                                            ->label('Maturity Date')
                                                            ->date('M d, Y')
                                                            ->placeholder('-'),

                                                        TextEntry::make('transferred_amount')
                                                            ->label('Transferred to Regular Savings')
                                                            ->money('PHP')
                                                            ->placeholder('Not transferred yet'),

                                                        TextEntry::make('transfer_date')
                                                            ->label('Transfer Date')
                                                            ->dateTime('M d, Y h:i A')
                                                            ->placeholder('-'),

                                                        TextEntry::make('notes')
                                                            ->label('Notes')
                                                            ->placeholder('-'),
                                                    ])
                                                    ->table([
                                                        TableColumn::make('Amount')->width('10rem'),
                                                        TableColumn::make('Term')->width('8rem'),
                                                        TableColumn::make('Status')->width('9rem'),
                                                        TableColumn::make('Maturity Option')->width('14rem'),
                                                        TableColumn::make('Deposit Date')->width('12rem'),
                                                        TableColumn::make('Maturity Date')->width('10rem'),
                                                        TableColumn::make('Transferred to Regular Savings')->width('14rem'),
                                                        TableColumn::make('Transfer Date')->width('12rem'),
                                                        TableColumn::make('Notes')->width('18rem'),
                                                    ])
                                                    ->contained()
                                                    ->columnSpanFull(),
                                            ])
                                            ->collapsible()
                                            ->collapsed()
                                            ->visible(function ($record): bool {
                                                return count(static::getTimeDepositDisplayTransactions((int) $record->profile_id)) > 3;
                                            }),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
