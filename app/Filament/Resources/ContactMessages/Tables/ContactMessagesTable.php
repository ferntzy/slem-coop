<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Enums\RecordActionsPosition;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('message')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->message),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'unread',
                        'success' => 'read',
                        'primary' => 'replied',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'unread'  => 'Unread',
                        'read'    => 'Read',
                        'replied' => 'Replied',
                    ]),
            ])

            ->recordActions([
                ActionGroup::make([
                    Action::make('markRead')
                        ->label('Mark as Read')
                        ->icon('heroicon-o-check')
                        ->action(fn ($record) => $record->update([
                            'status'  => 'read',
                            'read_at' => now(),
                        ]))
                        ->visible(fn ($record) => $record->status === 'unread'),

                    Action::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->delete()),
                ])
            ], position: RecordActionsPosition::BeforeColumns);
    }
}