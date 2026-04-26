<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Widgets\TableWidget as BaseWidget;

class BranchCrudWidget extends BaseWidget
{
    protected static ?string $heading = 'Branches';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(Branch::query()->orderBy('name'))
            ->columns([
                TextColumn::make('name')->label('Name')->searchable()->sortable(),
                TextColumn::make('code')->label('Code')->searchable()->sortable(),
                TextColumn::make('address')->label('Address')->limit(35),
                TextColumn::make('contact_no')->label('Contact'),
                BooleanColumn::make('is_active')->label('Active'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('code')->required()->maxLength(50),
                        Forms\Components\TextInput::make('address')->required()->maxLength(255),
                        Forms\Components\TextInput::make('contact_no')->maxLength(30),
                        Forms\Components\Toggle::make('is_active')->label('Active')->default(true),
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('name')->required()->maxLength(255),
                        Forms\Components\TextInput::make('code')->required()->maxLength(50),
                        Forms\Components\TextInput::make('address')->required()->maxLength(255),
                        Forms\Components\TextInput::make('contact_no')->maxLength(30),
                        Forms\Components\Toggle::make('is_active')->label('Active'),
                    ]),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->filters([
                Filter::make('active')->query(fn ($query) => $query->where('is_active', true))->label('Only Active'),
            ])
            ->defaultSort('name');
    }
}
