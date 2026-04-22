<?php

namespace App\Filament\Resources\LoanTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoanTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Loan Type')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap()
                    ->alignLeft()
                    ->searchable()
                    ->width('250px'),

                TextColumn::make('max_interest_rate')
                    ->label('Interest')
                    ->suffix('%')
                    ->alignCenter()
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('max_term_months')
                    ->label('Max Term')
                    ->formatStateUsing(fn ($state) => $state.' months')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('max_amount')
                    ->label('Max Amount')
                    ->money('PHP', true)
                    ->alignCenter()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter()
                    ->sortable(),
            ])

            ->striped() // cleaner rows
            ->defaultSort('name')

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
