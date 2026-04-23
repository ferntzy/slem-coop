<?php

namespace App\Filament\Resources\PenaltyRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PenaltyRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->label('Rule Name')
                    ->helperText('A short identifier for this penalty rule (e.g. "Standard Monthly Penalty").')
                    ->required()
                    ->maxLength(100)
                    ->prefixIcon('heroicon-o-tag')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Description')
                    ->helperText('Optional notes about when or how this rule is applied.')
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull(),

                TextInput::make('grace_period_days')
                    ->label('Grace Period (Days)')
                    ->helperText('Days after the due date before this penalty fires.')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(365)
                    ->default(5)
                    ->suffix('days')
                    ->prefixIcon('heroicon-o-calendar-days')
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('rate')
                    ->label('Penalty Rate')
                    ->helperText('Percentage charged as penalty on the selected base amount.')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->default(5)
                    ->suffix('%')
                    ->prefixIcon('heroicon-o-percent-badge')
                    ->required()
                    ->columnSpanFull(),

                Select::make('type')
                    ->label('Penalty Base')
                    ->helperText('The amount the penalty rate is applied against.')
                    ->options([
                        'principal' => 'Principal Interest (outstanding principal)',
                        'balance' => 'Outstanding Balance (principal + interest)',
                    ])
                    ->default('principal')
                    ->prefixIcon('heroicon-o-calculator')
                    ->required()
                    ->columnSpanFull(),

                Select::make('frequency')
                    ->label('Penalty Frequency')
                    ->helperText('How often the penalty is charged on the overdue account.')
                    ->options([
                        'one_time' => 'One-Time (charged once at grace period breach)',
                        'daily' => 'Daily',
                        'monthly' => 'Monthly',
                    ])
                    ->default('monthly')
                    ->prefixIcon('heroicon-o-arrow-path')
                    ->required()
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Only active rules can be assigned to loan products.')
                    ->default(true)
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-x-mark')
                    ->columnSpanFull(),

            ])
            ->columns(1);
    }
}
