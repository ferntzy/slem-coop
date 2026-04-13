<?php

namespace App\Filament\Resources\ShareCapitalTransactions\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

class ShareCapitalTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Share Capital Transaction')
                ->schema([
                    Select::make('profile_id')
                        ->label('Member (Profile)')
                        ->relationship(
                            name: 'profile',
                            titleAttribute: 'email',
                            modifyQueryUsing: fn ($query) => $query->whereHas('memberDetail') // members only
                        )
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => $record->full_name . ' — ' . $record->email
                        )
                        ->required(),

                    Select::make('type')
                        ->label('Type')
                        ->options([
                            'deposit' => 'Deposit',
                            'withdraw' => 'Withdraw',
                            'adjustment_credit' => 'Adjustment (+)',
                            'adjustment_debit' => 'Adjustment (-)',
                        ])
                        ->required(),

                    Hidden::make('direction')
                        ->dehydrated(true),

                    TextInput::make('amount')
                        ->numeric()
                        ->minValue(0.01)
                        ->required(),

                    DatePicker::make('transaction_date')
                        ->default(fn () => now())
                        ->required(),

                    TextInput::make('reference_no')
                        ->label('Reference No.')
                        ->maxLength(50),

                    Textarea::make('notes')
                        ->rows(3)
                        ->columnSpanFull(),

                    Hidden::make('posted_by_user_id')
                        ->default(fn () => auth()->id())
                        ->dehydrated(true),
                ])
                ->columns(2),
        ]);
    }
}