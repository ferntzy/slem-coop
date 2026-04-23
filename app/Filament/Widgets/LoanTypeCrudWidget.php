<?php

namespace App\Filament\Widgets;

use App\Models\LoanType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LoanTypeCrudWidget extends BaseWidget
{
    protected static ?string $heading = 'Loan Types';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(LoanType::query()->orderBy('name'))
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('description')->limit(40),
                TextColumn::make('min_amount')->label('Min Amount')->money('PHP')->sortable(),
                TextColumn::make('max_amount')->label('Max Amount')->money('PHP')->sortable(),
                TextColumn::make('max_interest_rate')->label('Max Interest Rate (%)')->suffix('%')->sortable(),
                TextColumn::make('max_term_months')->label('Max Term (months)'),
                BooleanColumn::make('is_active')->label('Active'),
            ])
            ->headerActions([
                CreateAction::make()->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->maxLength(1000),
                    Forms\Components\TextInput::make('min_amount')->numeric()->required(),
                    Forms\Components\TextInput::make('max_amount')->numeric()->required(),
                    Forms\Components\TextInput::make('max_interest_rate')
                        ->numeric()
                        ->required()
                        ->label('Max Interest Rate'),
                    Forms\Components\Select::make('amount_calculation_type')
                        ->options([
                            'fixed' => 'Fixed',
                            'multiplier' => 'Multiplier',
                            // add other types as needed
                        ])
                        ->required()
                        ->label('Amount Calculation Type'),
                    Forms\Components\TextInput::make('max_term_months')->numeric()->required(),
                    Forms\Components\Toggle::make('requires_collateral')->label('Requires Collateral')->default(false),
                    Forms\Components\TextInput::make('collateral_threshold')->numeric(),
                    Forms\Components\Toggle::make('is_active')->default(true),
                ]),
            ])
            ->actions([
                EditAction::make()->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->maxLength(1000),
                    Forms\Components\TextInput::make('min_amount')->numeric()->required(),
                    Forms\Components\TextInput::make('max_amount')->numeric()->required(),
                    Forms\Components\TextInput::make('max_interest_rate')
                        ->numeric()
                        ->required()
                        ->label('Max Interest Rate'),
                    Forms\Components\Select::make('amount_calculation_type')
                        ->options([
                            'fixed' => 'Fixed',
                            'multiplier' => 'Multiplier',
                            // add other types as needed
                        ])
                        ->required()
                        ->label('Amount Calculation Type'),
                    Forms\Components\TextInput::make('max_term_months')->numeric()->required(),
                    Forms\Components\Toggle::make('requires_collateral')->label('Requires Collateral'),
                    Forms\Components\TextInput::make('collateral_threshold')->numeric(),
                    Forms\Components\Toggle::make('is_active'),
                ]),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }
}
