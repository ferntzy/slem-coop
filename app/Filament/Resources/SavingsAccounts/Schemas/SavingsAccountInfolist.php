<?php

namespace App\Filament\Resources\SavingsAccounts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SavingsAccountInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Savings Account')
                    ->schema([
                        TextEntry::make('member.profile.full_name')
                            ->label('Member')
                            ->placeholder('—'),

                        TextEntry::make('member.member_no')
                            ->label('Member No.')
                            ->placeholder('—'),

                        TextEntry::make('account_number')
                            ->label('Account Number')
                            ->placeholder('—'),

                        TextEntry::make('savingsType.name')
                            ->label('Savings Type')
                            ->placeholder('—'),

                        TextEntry::make('terms')
                            ->label('Term (Months)')
                            ->placeholder('—'),

                        TextEntry::make('amount')
                            ->label('Initial Deposit')
                            ->money('PHP'),

                        TextEntry::make('balance')
                            ->label('Balance')
                            ->money('PHP'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Approved' => 'success',
                                'Pending' => 'warning',
                                'Rejected' => 'danger',
                                default => 'gray',
                            }),

                        TextEntry::make('proof_of_payment')
                            ->label('Proof of Payment')
                            ->placeholder('—'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('M d, Y h:i A')
                            ->placeholder('—'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
