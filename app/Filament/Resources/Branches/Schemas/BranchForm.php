<?php

namespace App\Filament\Resources\Branches\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class BranchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Branch Information')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('code')
                        ->label('Branch Code')
                        ->maxLength(50)
                        ->unique(ignoreRecord: true),

                    TextInput::make('address')
                        ->maxLength(255),

                    TextInput::make('contact_no')
                        ->label('Contact Number')
                        ->maxLength(50),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true),
                ])
                ->columns(2),
            ]);
    }
}
