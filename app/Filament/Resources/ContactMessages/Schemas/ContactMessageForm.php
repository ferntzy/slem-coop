<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sender Information')
                    ->columns(1)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('subject')
                            ->required()
                            ->maxLength(255),
                    ]),

                Section::make('Message Details')
                    ->schema([
                        Textarea::make('message')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->options([
                                'unread'  => 'Unread',
                                'read'    => 'Read',
                                'replied' => 'Replied',
                            ])
                            ->required(),
                    ]),
            ]);
    }
}