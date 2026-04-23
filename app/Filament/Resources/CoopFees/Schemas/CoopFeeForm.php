<?php

namespace App\Filament\Resources\CoopFeeResource\Forms;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;

class CoopFeeForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('type')
                            ->label('Fee Type')
                            ->options([
                                'shared_capital' => 'Shared Capital (%)',
                                'insurance' => 'Insurance (Fixed ₱)',
                                'processing_fee' => 'Processing Fee (Fixed ₱)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($set) => [
                                $set('percentage', null),
                                $set('amount', null),
                            ]),

                        TextInput::make('percentage')
                            ->label('Percentage (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->visible(fn ($get) => $get('type') === 'shared_capital')
                            ->required(fn ($get) => $get('type') === 'shared_capital'),

                        TextInput::make('amount')
                            ->label('Fixed Amount (₱)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('₱')
                            ->visible(fn ($get) => $get('type') !== 'shared_capital')
                            ->required(fn ($get) => $get('type') !== 'shared_capital'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Fee description')
                            ->maxLength(255),

                        Toggle::make('status')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
