<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
        return round((float) $transactions->sum('deposit') - (float) $transactions->sum('withdrawal'), 2);
    }

    protected static function getTransactionAmount(SavingsAccountTransaction $transaction): float
    {
        $depositAmount = (float) ($transaction->deposit ?? 0);

        if ($depositAmount > 0) {
            return round($depositAmount, 2);
        }

        return round((float) ($transaction->withdrawal ?? 0), 2);
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

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function getRegularSavingsDisplayTransactions(int $profileId): array
    {
        return static::getSavingsTransactions($profileId, static::getRegularSavingsType())
            ->map(function (SavingsAccountTransaction $transaction): array {
                return [
                    'id' => (int) $transaction->id,
                    'type' => $transaction->type,
                    'amount' => static::getTransactionAmount($transaction),
                    'status' => $transaction->status,
                    'transaction_date' => $transaction->transaction_date,
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
                            ]),

                        Tab::make('Spouse & Co-Makers')
                            ->icon('heroicon-o-users')
                            ->schema([
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

                                        TextEntry::make('regular_savings_balance')
                                            ->label('Current Balance')
                                            ->state(function ($record): float {
                                                $transactions = static::getSavingsTransactions(
                                                    (int) $record->profile_id,
                                                    static::getRegularSavingsType()
                                                );

                                                return static::getBalance($transactions);
                                            })
                                            ->money('PHP'),
                                    ])
                                    ->columns(2),

                                Section::make('Regular Savings Transactions')
                                    ->schema([
                                        RepeatableEntry::make('regular_savings_transactions')
                                            ->label('Latest 3 Transactions')
                                            ->state(function ($record): array {
                                                $transactions = collect(
                                                    static::getRegularSavingsDisplayTransactions((int) $record->profile_id)
                                                );

                                                return static::getLatestTransactions($transactions);
                                            })
                                            ->schema([
                                                TextEntry::make('type')
                                                    ->badge()
                                                    ->color(fn ($state) => match ($state) {
                                                        'Deposit' => 'success',
                                                        'Withdrawal' => 'warning',
                                                        'Interest' => 'info',
                                                        default => 'gray',
                                                    }),

                                                TextEntry::make('amount')
                                                    ->label('Amount')
                                                    ->money('PHP'),

                                                TextEntry::make('status')
                                                    ->badge()
                                                    ->placeholder('-'),

                                                TextEntry::make('transaction_date')
                                                    ->label('Transaction Date')
                                                    ->dateTime('M d, Y h:i A'),

                                                TextEntry::make('notes')
                                                    ->label('Notes')
                                                    ->placeholder('-'),
                                            ])
                                            ->columns(5)
                                            ->contained()
                                            ->columnSpanFull(),

                                        Section::make('More Regular Savings Transactions')
                                            ->schema([
                                                RepeatableEntry::make('older_regular_savings_transactions')
                                                    ->label('Older Transactions')
                                                    ->state(function ($record): array {
                                                        $transactions = collect(
                                                            static::getRegularSavingsDisplayTransactions((int) $record->profile_id)
                                                        );

                                                        return static::getOlderTransactions($transactions);
                                                    })
                                                    ->schema([
                                                        TextEntry::make('type')
                                                            ->badge()
                                                            ->color(fn ($state) => match ($state) {
                                                                'Deposit' => 'success',
                                                                'Withdrawal' => 'warning',
                                                                'Interest' => 'info',
                                                                default => 'gray',
                                                            }),

                                                        TextEntry::make('amount')
                                                            ->label('Amount')
                                                            ->money('PHP'),

                                                        TextEntry::make('status')
                                                            ->badge()
                                                            ->placeholder('-'),

                                                        TextEntry::make('transaction_date')
                                                            ->label('Transaction Date')
                                                            ->dateTime('M d, Y h:i A'),

                                                        TextEntry::make('notes')
                                                            ->label('Notes')
                                                            ->placeholder('-'),
                                                    ])
                                                    ->columns(5)
                                                    ->contained()
                                                    ->columnSpanFull(),
                                            ])
                                            ->collapsible()
                                            ->collapsed()
                                            ->visible(function ($record): bool {
                                                return count(static::getRegularSavingsDisplayTransactions((int) $record->profile_id)) > 3;
                                            }),
                                    ]),

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
                                    ->schema([
                                        RepeatableEntry::make('time_deposit_transactions')
                                            ->label('Latest 3 Transactions')
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
                                            ->columns(9)
                                            ->contained()
                                            ->columnSpanFull(),

                                        Section::make('More Time Deposit Transactions')
                                            ->schema([
                                                RepeatableEntry::make('older_time_deposit_transactions')
                                                    ->label('Older Transactions')
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
                                                    ->columns(9)
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
