<?php

namespace App\Filament\Resources\MemberDetails\Tables;

use App\Models\Profile;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
        return $amount !== null ? '₱' . number_format($amount, 2) : '—';
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
            TextColumn::make('profile.full_name')
                ->label('Member')
                ->searchable()
                ->sortable(),

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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    Action::make('add_savings')
                        ->label('Add Savings')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Select::make('profile_id')
                            ->label('Member')
                            ->options(function () {
                                return Profile::where(function ($query) {
                                        $query->whereHas('memberDetail', function (Builder $q) {
                                            $q->where('status', 'Active');
                                        })->orWhereDoesntHave('memberDetail');
                                    })
                                    ->whereHas('user', function (Builder $q) {
                                        $q->where('is_active', true);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($profile) {
                                        $label = $profile->full_name . ' — ' . ($profile->memberDetail?->member_no ?? 'N/A');
                                        return [$profile->profile_id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function($record) {
                                return $record->full_name;
                            })
                            ->disabled()
                            ->dehydrated(true),
                        Select::make('savings_type_id')
                            ->label('Savings Type')
                            ->relationship(
                                'savingsType',
                                'name',
                                fn (Builder $query) => $query->where('is_active', true)
                            )
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                $code = $record->code ? " ({$record->code})" : '';

                                return "{$record->name}{$code}";
                            })
                            ->searchable()
                            ->preload()
                            ->default(function($record) {
                                return $record->savingsType->id=2 ?? null;
                            })
                            ->disabled()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
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
                            ->numeric()
                            ->minValue(function (callable $get): ?string {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return null;
                                }

                                $min = (int) ($type->minimum_terms ?? 0);

                                return $min > 0 ? $min : null;
                            })
                            ->rules(function (callable $get) {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return [];
                                }

                                $min = (int) ($type->minimum_terms ?? 2);

                                return $min > 0 ? ["min:{$min}"] : [];
                            })
                            ->helperText(function (callable $get): ?string {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return null;
                                }

                                $min = (int) ($type->minimum_terms ?? 0);

                                return $min > 0 ? "Minimum term: {$min} month(s)." : null;
                            })
                            ->required(),

                        TextInput::make('type')
                            ->label('Type')
                            ->default('Deposit')
                            ->disabled()
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

                                return $min > 0 ? 'Minimum initial deposit: ' . static::money($min) : null;
                            })
                            ->required(),
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
                        ->action(function ($record, array $data){
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
                                    'transaction_date' => now(),
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
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Select::make('profile_id')
                            ->label('Member')
                            ->options(function () {
                                return Profile::where(function ($query) {
                                        $query->whereHas('memberDetail', function (Builder $q) {
                                            $q->where('status', 'Active');
                                        })->orWhereDoesntHave('memberDetail');
                                    })
                                    ->whereHas('user', function (Builder $q) {
                                        $q->where('is_active', true);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($profile) {
                                        $label = $profile->full_name . ' — ' . ($profile->memberDetail?->member_no ?? 'N/A');
                                        return [$profile->profile_id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function($record) {
                                return $record->full_name;
                            })
                            ->disabled()
                            ->dehydrated(true),
                        Select::make('savings_type_id')
                            ->label('Savings Type')
                            ->relationship(
                                'savingsType',
                                'name',
                                fn (Builder $query) => $query->where('is_active', true)
                            )
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                $code = $record->code ? " ({$record->code})" : '';

                                return "{$record->name}{$code}";
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(function($record) {
                                return $record->savingsType->id=1 ?? null;
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
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
                            ->disabled()
                            ->required(),

                        TextInput::make('terms')
                            ->label('Term (Months)')
                            ->numeric()
                            ->minValue(function (callable $get): ?string {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return null;
                                }

                                $min = (int) ($type->minimum_terms ?? 0);

                                return $min > 0 ? $min : null;
                            })
                            ->rules(function (callable $get) {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return [];
                                }

                                $min = (int) ($type->minimum_terms ?? 2);

                                return $min > 0 ? ["min:{$min}"] : [];
                            })
                            ->helperText(function (callable $get): ?string {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return null;
                                }

                                $min = (int) ($type->minimum_terms ?? 0);

                                return $min > 0 ? "Minimum term: {$min} month(s)." : null;
                            })
                            ->required(),

                        TextInput::make('type')
                            ->label('Type')
                            ->default('Deposit')
                            ->disabled()
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

                                return $min > 0 ? 'Minimum initial deposit: ' . static::money($min) : null;
                            })
                            ->required(),
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
                        ->action(function ($record, array $data){
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
                                    'transaction_date' => now(),
                                    'notes' => $data['notes'] ?? null,
                                    'posted_by_user_id' => auth()->id(),
                                ]);

                            Notification::make()
                                ->title('Savings Approved')
                                ->success()
                                ->send();
                        }),

                        Action::make('add_withdrawal')
                        ->label('Add Withdrawal')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->form([
                            Select::make('profile_id')
                            ->label('Member')
                            ->options(function () {
                                return Profile::where(function ($query) {
                                        $query->whereHas('memberDetail', function (Builder $q) {
                                            $q->where('status', 'Active');
                                        })->orWhereDoesntHave('memberDetail');
                                    })
                                    ->whereHas('user', function (Builder $q) {
                                        $q->where('is_active', true);
                                    })
                                    ->get()
                                    ->mapWithKeys(function ($profile) {
                                        $label = $profile->full_name . ' — ' . ($profile->memberDetail?->member_no ?? 'N/A');
                                        return [$profile->profile_id => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(function($record) {
                                return $record->full_name;
                            })
                            ->disabled()
                            ->dehydrated(true),
                        Select::make('savings_type_id')
                            ->label('Savings Type')
                            ->relationship(
                                'savingsType',
                                'name',
                                fn (Builder $query) => $query->where('is_active', true)
                            )
                            ->getOptionLabelFromRecordUsing(function ($record): string {
                                $code = $record->code ? " ({$record->code})" : '';

                                return "{$record->name}{$code}";
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->default(function($record) {
                                return $record->savingsType ?? null;
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
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
                            ->disabled()
                            ->required(),

                        Placeholder::make('savings_table')
                            ->label('Available Withdrawal')
                            ->content(function (callable $get) {
                                $profileId = $get('profile_id');
                                $savings_type_id = $get('savings_type_id');
                                $savings_transaction_id = $get('savings_account_transaction.id');

                                if (! $profileId) {
                                    return "Please select a savings type to view available withdrawal amount.";
                                }
                                $savings = SavingsAccountTransaction::where('profile_id', $profileId)
                                    ->whereHas('savingsType', function (Builder $query) use ($savings_type_id) {
                                        $query->where('id', $savings_type_id);
                                    })
                                    ->get();
                                if ($savings->isEmpty()) {
                                    return "No Savings Account Transactions found for the selected member and savings type.";
                                }

                                $rows = "";
                                if ($savings_type_id === 2) {
                                    $totalDeposit = $savings->sum('deposit');
                                    $totalWithdrawal = $savings->sum('withdrawal');
                                    $available = $totalDeposit - $totalWithdrawal;
                                    foreach ($savings as $transaction) {
                                        $maturityDate = date('Y-m-d', strtotime("$savings->created_at +$savings->terms months"));
                                    }
                                }
                            }),

                        TextInput::make('terms')
                            ->label('Term (Months)')
                            ->numeric()
                            ->minValue(function (callable $get): ?string {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return null;
                                }

                                $min = (int) ($type->minimum_terms ?? 0);

                                return $min > 0 ? $min : null;
                            })
                            ->rules(function (callable $get) {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return [];
                                }

                                $min = (int) ($type->minimum_terms ?? 2);

                                return $min > 0 ? ["min:{$min}"] : [];
                            })
                            ->helperText(function (callable $get): ?string {
                                $type = static::getSavingsType($get);

                                if (! $type) {
                                    return null;
                                }

                                $min = (int) ($type->minimum_terms ?? 0);

                                return $min > 0 ? "Minimum term: {$min} month(s)." : null;
                            })
                            ->required(),

                        TextInput::make('type')
                            ->label('Type')
                            ->default('Withdrawal')
                            ->disabled()
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

                                return $min > 0 ? 'Minimum initial deposit: ' . static::money($min) : null;
                            })
                            ->required(),
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
                        ->action(function ($record, array $data){
                            if (! (auth()->user()?->hasAnyRole(['Admin', 'super_admin']) ?? false)) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $type = $data['type'];
                                SavingsAccountTransaction::create([
                                    'profile_id' => $data['profile_id'],
                                    'savings_type_id' => $data['savings_type_id'],
                                    'withdrawal' => $data['amount'],
                                    'transaction_date' => now(),
                                    'notes' => $data['notes'] ?? null,
                                    'posted_by_user_id' => auth()->id(),
                                ]);

                            Notification::make()
                                ->title('Savings Approved')
                                ->success()
                                ->send();
                        })
                ])

            ])
            ->recordActionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
