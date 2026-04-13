<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                Tab::make('Account')
                    ->schema([
                        TextEntry::make('user_id')
                        ->label('User ID')
                        ->color('warning'),
                        TextEntry::make('username')
                        ->label('Username')
                        ->placeholder('-')
                        ->color('warning'),
                        TextEntry::make('created_at')
                        ->dateTime()
                        ->color('warning'),
                        TextEntry::make('updated_at')
                        ->dateTime()
                        ->color('warning'),
                    ])
                    ->columns(2),

                Tab::make('Profile (Login Email is here)')
                    ->schema([
                        TextEntry::make('profile.full_name')
                            ->label('Full Name')
                            ->color('warning'),

                        TextEntry::make('profile.email')
                            ->label('Login Email')
                            ->color('warning'),
                    ])
                    ->columns(2),

                Tab::make('Role')
                    ->schema([
                        TextEntry::make('profile.role.name')
                            ->label('Role')
                            ->color('warning'),
                    ]),
                ])->columnSpanFULL()->vertical(),
            ]);
    }
}
