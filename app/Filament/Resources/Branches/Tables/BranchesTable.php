<?php

namespace App\Filament\Resources\Branches\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch_id')
                ->label('ID')
                ->sortable(),

            TextColumn::make('name')
                ->searchable()
                ->sortable(),

            TextColumn::make('code')
                ->label('Code')
                ->searchable()
                ->sortable(),

            TextColumn::make('address')
                ->limit(40),

            TextColumn::make('contact_no')
                ->label('Contact'),

            IconColumn::make('is_active')
                ->label('Active')
                ->boolean()
                ->sortable(),
            ])->defaultSort('branch_id', 'desc')
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
