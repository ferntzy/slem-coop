<?php

namespace App\Filament\Resources\MembershipTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class MembershipTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('membership_type_id')
                ->label('ID')
                ->sortable(),

            TextColumn::make('name')
                ->searchable()
                ->sortable(),

            TextColumn::make('description')
                ->limit(50),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
        ])
        ->defaultSort('membership_type_id', 'desc')
            
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
