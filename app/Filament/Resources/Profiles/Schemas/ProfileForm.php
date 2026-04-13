<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(100),

                TextInput::make('middle_name')
                    ->maxLength(45),

                TextInput::make('last_name')
                    ->required()
                    ->maxLength(45),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('mobile_number')
                    ->maxLength(45),

                DatePicker::make('birthdate'),

                Select::make('sex')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                    ]),

                Select::make('civil_status')
                    ->options([
                        'Single' => 'Single',
                        'Married' => 'Married',
                        'Widowed' => 'Widowed',
                        'Separated' => 'Separated',
                    ]),

                TextInput::make('tin')
                    ->label('TIN')
                    ->maxLength(45),

                TextInput::make('address')
                    ->maxLength(255),

                Select::make('roles_id')
                    ->label('Role')
                    ->relationship('role', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextColumn::make('system_roles')
                    ->label('System Role')
                    ->badge(),

        ]);
    }
}
