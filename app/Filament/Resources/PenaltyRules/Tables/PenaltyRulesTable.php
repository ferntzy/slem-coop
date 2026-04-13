<?php

namespace App\Filament\Resources\PenaltyRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PenaltyRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Rule Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('grace_period_days')
                    ->label('Grace Period')
                    ->suffix(' days')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('rate')
                    ->label('Rate')
                    ->suffix('%')
                    ->sortable()
                    ->alignCenter(),

                BadgeColumn::make('type')
                    ->label('Base')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'principal' => 'Principal',
                        'balance'   => 'Balance',
                        default     => $state,
                    })
                    ->colors([
                        'info'    => 'principal',
                        'warning' => 'balance',
                    ]),

                BadgeColumn::make('frequency')
                    ->label('Frequency')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'monthly'  => 'Monthly',
                        'daily'    => 'Daily',
                        'one_time' => 'One-Time',
                        default    => $state,
                    })
                    ->colors([
                        'success' => 'monthly',
                        'warning' => 'daily',
                        'gray'    => 'one_time',
                    ]),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-exclamation-triangle')
            ->emptyStateHeading('No penalty rules yet')
            ->emptyStateDescription('Create your first penalty rule to assign it to loan products.');
    }
}