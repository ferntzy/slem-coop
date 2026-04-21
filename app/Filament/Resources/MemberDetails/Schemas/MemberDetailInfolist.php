<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MemberDetailInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->tabs([
                        Tab::make('Personal Details')
                            ->icon('heroicon-o-user-circle')
                            ->schema([
                                Section::make('Member Information')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('profile.mobile_number')
                                            ->label('Mobile Number')
                                            ->icon('heroicon-o-phone'),

                                        TextEntry::make('profile.email')
                                            ->label('Email')
                                            ->icon('heroicon-o-envelope')
                                            ->copyable(),

                                        TextEntry::make('member_no')
                                            ->label('Member Number')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('occupation')
                                            ->label('Occupation'),

                                        TextEntry::make('employer_name')
                                            ->label('Employer'),

                                        TextEntry::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('membership_Status')
                                            ->label('Membership Status')
                                            ->getStateUsing(fn ($record) => $record->membershipStatus())
                                            ->badge()
                                            ->color(fn ($state) => $state === 'Active' ? 'success' : 'warning'),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make('Spouse & Co-Makers')
                            ->icon('heroicon-o-users')
                            ->schema([
                                Section::make('Spouse Information')
                                    ->schema([
                                        TextEntry::make('spouse.full_name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->color('success')
                                            ->placeholder('—'),

                                        TextEntry::make('spouse.birthdate')
                                            ->label('Birthdate')
                                            ->placeholder('—'),

                                        TextEntry::make('spouse.occupation')
                                            ->label('Occupation')
                                            ->placeholder('—'),

                                        TextEntry::make('spouse.employer_name')
                                            ->label('Employer')
                                            ->placeholder('—'),

                                        TextEntry::make('spouse.source_of_income')
                                            ->label('Source of Income')
                                            ->placeholder('—'),

                                        TextEntry::make('spouse.monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success')
                                            ->placeholder('—'),
                                    ])
                                    ->columns(3),

                                Section::make('Co-Makers')
                                    ->schema([
                                        RepeatableEntry::make('coMakers')
                                            ->label('Co-Makers')
                                            ->schema([
                                                TextEntry::make('full_name')
                                                    ->label('Full Name')
                                                    ->weight('bold')
                                                    ->color('info'),
                                                TextEntry::make('relationship')
                                                    ->label('Relationship')
                                                    ->badge(),
                                                TextEntry::make('contact_number')
                                                    ->label('Contact Number')
                                                    ->icon('heroicon-o-phone'),
                                                TextEntry::make('address')
                                                    ->label('Address'),
                                                TextEntry::make('occupation')
                                                    ->label('Occupation'),
                                                TextEntry::make('employer_name')
                                                    ->label('Employer'),
                                                TextEntry::make('monthly_income')
                                                    ->label('Monthly Income')
                                                    ->money('PHP')
                                                    ->weight('bold')
                                                    ->color('success'),
                                            ])
                                            ->columns(3)
                                            ->contained(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Employment & Identification')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Employment Information')
                                    ->schema([
                                        TextEntry::make('occupation')
                                            ->label('Occupation')
                                            ->weight('bold'),

                                        TextEntry::make('employer_name')
                                            ->label('Employer / Business Name')
                                            ->weight('bold')
                                            ->color('info'),

                                        TextEntry::make('source_of_income')
                                            ->label('Source of Income'),

                                        TextEntry::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('monthly_income_range')
                                            ->label('Monthly Income Range')
                                            ->badge()
                                            ->color('secondary'),
                                    ])
                                    ->columns(2),

                                Section::make('Identification')
                                    ->schema([
                                        TextEntry::make('id_type')
                                            ->label('ID Type')
                                            ->badge(),

                                        TextEntry::make('id_number')
                                            ->label('ID Number')
                                            ->copyable(),
                                    ])
                                    ->columns(2),

                                Section::make('Emergency Contact')
                                    ->schema([
                                        TextEntry::make('emergency_full_name')
                                            ->label('Full Name')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('emergency_phone')
                                            ->label('Phone Number')
                                            ->icon('heroicon-o-phone'),

                                        TextEntry::make('emergency_relationship')
                                            ->label('Relationship')
                                            ->badge(),
                                    ])
                                    ->columns(3),

                                Section::make('Household Information')
                                    ->schema([
                                        TextEntry::make('dependents_count')
                                            ->label('Number of Dependents')
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('children_in_school_count')
                                            ->label('Children in School')
                                            ->badge()
                                            ->color('warning'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Shared Capital Transactions')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                TextEntry::make('share_capital_balance')
                                    ->label('Share Capital Balance')
                                    ->money('PHP')
                                    ->weight('bold')
                                    ->color('success')
                                    ->size('lg'),

                                RepeatableEntry::make('sharedCapitalTransactions')
                                    ->label('Share Capital Transactions')
                                    ->schema([
                                        TextEntry::make('transaction_date')
                                            ->label('Date')
                                            ->date('F j, Y'),
                                        TextEntry::make('amount')
                                            ->label('Amount')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),
                                        TextEntry::make('direction')
                                            ->label('Direction')
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'credit' => 'success',
                                                'debit' => 'danger',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('type')
                                            ->label('Type')
                                            ->badge(),
                                        TextEntry::make('reference_no')
                                            ->label('Reference No.')
                                            ->placeholder('—'),
                                        TextEntry::make('notes')
                                            ->label('Notes')
                                            ->placeholder('—'),
                                        TextEntry::make('postedBy.name')
                                            ->label('Posted By')
                                            ->placeholder('—'),
                                    ])
                                    ->columns(3)
                                    ->contained(),
                            ]),
                        Tab::make('Savings')
                            ->icon('heroicon-o-building-library')
                            ->schema([
                                Section::make('Savings')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Member Name')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('amount')
                                            ->label('Balance')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'Approved' => 'success',
                                                'Active' => 'success', // 🔥 FIXED
                                                'Pending' => 'warning',
                                                'Rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('profile.created_at')
                                            ->label('Created')
                                            ->dateTime('M d, Y h:i A'),
                                    ])
                                    ->columns(2),

                                Section::make('Time Deposits')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Member Name')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('amount')
                                            ->label('Balance')
                                            ->money('PHP')
                                            ->weight('bold')
                                            ->color('success'),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'Approved' => 'success',
                                                'Active' => 'success', // 🔥 FIXED HERE TOO
                                                'Pending' => 'warning',
                                                'Rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('created_at')
                                            ->label('Created')
                                            ->dateTime('M d, Y h:i A'),
                                    ])
                                    ->columns(2),

                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
