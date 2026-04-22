<?php

namespace App\Filament\Resources\ShareCapitalTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShareCapitalTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('profile.full_name')
                    ->label('Member')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),

                TextColumn::make('direction')
                    ->label('Direction')
                    ->badge(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->numeric(2)
                    ->sortable(),

                TextColumn::make('postedBy.name')
                    ->label('Posted By')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]); // no bulk actions for financial records
    }
}
