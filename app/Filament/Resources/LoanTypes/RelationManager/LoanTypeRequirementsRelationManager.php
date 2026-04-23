<?php

namespace App\Filament\Resources\LoanTypes\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoanTypeRequirementsRelationManager extends RelationManager
{
    protected static string $relationship = 'requirements';

    public function form(Schema $schema): Schema
    {
        return $form->schema([
            TextInput::make('code')
                ->label('Code')
                ->helperText('Example: gov_id, payslip, collateral_proof')
                ->required()
                ->maxLength(50),

            TextInput::make('label')
                ->label('Label')
                ->required()
                ->maxLength(150),

            Toggle::make('is_required')
                ->label('Required')
                ->default(true),

            TextInput::make('sort_order')
                ->label('Sort Order')
                ->numeric()
                ->default(0),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('code')->label('Code')->badge()->searchable(),
                TextColumn::make('label')->label('Label')->searchable(),
                TextColumn::make('is_required')->label('Required')->badge(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}
