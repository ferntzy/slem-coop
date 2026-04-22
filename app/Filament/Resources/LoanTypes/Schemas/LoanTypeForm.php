<?php

namespace App\Filament\Resources\LoanTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoanTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Loan Type Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(7.8)
                            ->columnSpan('Full'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),
                Group::make()
                    ->schema([
                        Section::make('Terms & Interest')
                            ->schema([
                                TextInput::make('max_interest_rate')
                                    ->label('Max Interest Rate (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->required()
                                    ->columnSpan(1), // side by side

                                TextInput::make('max_term_months')
                                    ->label('Max Term (Months)')
                                    ->numeric()
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('amount_calculation_type')
                                    ->label('Amount Calculation Type')
                                    ->required()
                                    ->options([
                                        'Fixed' => 'Fixed',
                                        'Multiplier' => 'Multiplier',
                                        'None' => 'None',
                                    ])
                                    ->reactive()
                                    ->columnSpan(1),

                                TextInput::make('amount_multiplier')
                                    ->label('Amount Multiplier (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->visible(fn (callable $get) => $get('amount_calculation_type') === 'Multiplier')
                                    ->columnSpan(1),
                            ])
                            ->columns(2), // two columns side by side

                        Section::make('Amount Limits')
                            ->schema([
                                TextInput::make('min_amount')
                                    ->label('Minimum Amount')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('max_amount')
                                    ->label('Maximum Amount')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->columnSpan(1),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
