<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
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
                            ->schema([
                                Section::make('Member Information')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Full Name'),

                                        TextEntry::make('profile.mobile_number')
                                            ->label('Mobile Number'),

                                        TextEntry::make('profile.email')
                                            ->label('Email'),

                                        TextEntry::make('member_no')
                                            ->label('Member Number'),

                                        TextEntry::make('occupation')
                                            ->label('Occupation'),

                                        TextEntry::make('employer_name')
                                            ->label('Employer'),

                                        TextEntry::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP'),

                                        TextEntry::make('membership_Status')
                                            ->label('Membership Status')
                                            ->getStateUsing(fn ($record) => $record->membershipStatus()),
                                    ])
                                    ->columns(3),
                            ]),

                        Tab::make('Spouse & Co-Makers')
                            ->schema([
                                Section::make('Spouse Information')
                                    ->schema([
                                        TextEntry::make('spouse.full_name')
                                            ->label('Full Name')
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
                                            ->placeholder('—'),
                                    ])
                                    ->columns(3),

                                Section::make('Co-Makers')
                                    ->schema([
                                        RepeatableEntry::make('coMakers')
                                            ->label('Co-Makers')
                                            ->schema([
                                                TextEntry::make('full_name')->label('Full Name'),
                                                TextEntry::make('relationship')->label('Relationship'),
                                                TextEntry::make('contact_number')->label('Contact Number'),
                                                TextEntry::make('address')->label('Address'),
                                                TextEntry::make('occupation')->label('Occupation'),
                                                TextEntry::make('employer_name')->label('Employer'),
                                                TextEntry::make('monthly_income')->label('Monthly Income')->money('PHP'),
                                            ])
                                            ->columns(3)
                                            ->contained(),
                                    ])
                                    ->columns(1),
                            ]),

                        Tab::make('Employment & Identification')
                            ->schema([
                                Section::make('Employment Information')
                                    ->schema([
                                        TextEntry::make('occupation')
                                            ->label('Occupation'),

                                        TextEntry::make('employer_name')
                                            ->label('Employer / Business Name'),

                                        TextEntry::make('source_of_income')
                                            ->label('Source of Income'),

                                        TextEntry::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->money('PHP'),

                                        TextEntry::make('monthly_income_range')
                                            ->label('Monthly Income Range'),
                                    ])
                                    ->columns(2),

                                Section::make('Identification')
                                    ->schema([
                                        TextEntry::make('id_type')
                                            ->label('ID Type'),

                                        TextEntry::make('id_number')
                                            ->label('ID Number'),
                                    ])
                                    ->columns(2),

                                Section::make('Emergency Contact')
                                    ->schema([
                                        TextEntry::make('emergency_full_name')
                                            ->label('Full Name'),

                                        TextEntry::make('emergency_phone')
                                            ->label('Phone Number'),

                                        TextEntry::make('emergency_relationship')
                                            ->label('Relationship'),
                                    ])
                                    ->columns(3),

                                Section::make('Household Information')
                                    ->schema([
                                        TextEntry::make('dependents_count')
                                            ->label('Number of Dependents'),

                                        TextEntry::make('children_in_school_count')
                                            ->label('Children in School'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tab::make('Shared Capital Transactions')
                            ->schema([
                                TextEntry::make('total_shared_capital')
                                    ->label('Total Shared Capital Transactions')
                                    ->getStateUsing(fn ($record) => '₱' . number_format(
                                        $record->loanApplications()->sum('shared_capital_fee'),
                                        2
                                    ))
                                    ->weight('bold'),

                                RepeatableEntry::make('loanApplications')
                                    ->label('Shared Capital per Loan')
                                    ->schema([
                                        TextEntry::make('type.name')->label('Loan Type'),
                                        TextEntry::make('shared_capital_fee')->label('Shared Capital Fee')->money('PHP'),
                                        TextEntry::make('amount_requested')->label('Amount Requested')->money('PHP'),
                                        TextEntry::make('term_months')->label('Term (Months)'),
                                        TextEntry::make('status')->label('Loan Status')
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'Pending'      => 'warning',
                                                'Under Review' => 'info',
                                                'Approved'     => 'success',
                                                'Rejected'     => 'danger',
                                                'Cancelled'    => 'gray',
                                                default        => 'gray',
                                            }),
                                        TextEntry::make('created_at')->label('Date Applied')->dateTime('F j, Y g:i A'),
                                        TextEntry::make('approved_at')->label('Approved At')->dateTime('F j, Y g:i A')->placeholder('Not yet approved'),
                                    ])
                                    ->columns(3)
                                    ->contained(),
                            ]),
                        Tab::make('Savings')
                            ->schema([
                                Section::make('Savings')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Member Name'),

                                        TextEntry::make('amount')
                                            ->label('Balance')
                                            ->money('PHP'),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'Approved' => 'success',
                                                'Pending' => 'warning',
                                                'Rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('profile.created_at')
                                            ->label('Created')
                                            ->dateTime('M d, Y h:i A'),
                                    ])->columns(2),
                                Section::make('Time Deposits')
                                    ->schema([
                                        TextEntry::make('profile.full_name')
                                            ->label('Member Name'),

                                        TextEntry::make('amount')
                                            ->label('Balance')
                                            ->money('PHP'),

                                        TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'Approved' => 'success',
                                                'Pending' => 'warning',
                                                'Rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('created_at')
                                            ->label('Created')
                                            ->dateTime('M d, Y h:i A'),
                                    ])->columns(2),

                            ])
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
