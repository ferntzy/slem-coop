<?php

namespace App\Filament\Resources\MembershipTypes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class MembershipTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Membership Type')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(45),

                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
            ]);
    }
}
