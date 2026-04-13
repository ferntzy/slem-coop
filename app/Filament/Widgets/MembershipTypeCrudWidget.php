<?php

namespace App\Filament\Widgets;

use App\Models\MembershipType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class MembershipTypeCrudWidget extends BaseWidget
{
    protected static ?string $heading = 'Membership Types';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(MembershipType::query()->orderBy('name'))
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('description')->limit(40),
                TextColumn::make('fee')->label('Fee')->money('PHP')->sortable(),
            ])
            ->headerActions([
                CreateAction::make()->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->maxLength(1000),
                    Forms\Components\TextInput::make('fee')->numeric()->required(),
                ]),
            ])
            ->actions([
                EditAction::make()->form([
                    Forms\Components\TextInput::make('name')->required()->maxLength(255),
                    Forms\Components\Textarea::make('description')->maxLength(1000),
                    Forms\Components\TextInput::make('fee')->numeric()->required(),
                ]),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('name');
    }
}
