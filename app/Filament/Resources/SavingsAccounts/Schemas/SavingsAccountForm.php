<?php

namespace App\Filament\Resources\SavingsAccounts\Schemas;

use App\Models\Profile;
use App\Models\SavingsType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SavingsAccountForm
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

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Savings Account Details')

                    ->schema([
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
                            ->default(function() {
                                if (auth()->check() && auth()->user()->profile_id) {
                                    return auth()->user()->profile_id;
                                }
                                return null;
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
                            ->minValue(0)
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

                        Select::make('type')
                            ->label('Deposit or Withdrawal')
                            ->options([
                                'Deposit' => 'Deposit',
                                'Withdrawal' => 'Withdrawal',
                            ]),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
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
                        TextInput::make('status')
                            ->label('Status')
                            ->default('Pending')
                            ->disabled()
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
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}

