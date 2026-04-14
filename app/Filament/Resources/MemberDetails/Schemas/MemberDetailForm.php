<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use App\Models\Profile;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class MemberDetailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Personal Details')
                        ->icon('heroicon-o-identification')
                        ->completedIcon('heroicon-o-hand-thumb-up')
                        ->schema([
                            Section::make('Member + Membership Info')
                                ->schema([
                                    Select::make('profile_id')
                                        ->label('Profile')
                                        ->relationship('profile', 'email')
                                        ->searchable()
                                        ->preload()
                                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->full_name . ' — ' . $record->email)
                                        ->required(),

                                    TextInput::make('member_no')
                                        ->label('Member No.')
                                        ->maxLength(45),

                                    Select::make('membership_type_id')
                                        ->label('Membership Type')
                                        ->relationship('membershipType', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('branch_id')
                                        ->relationship('branch', 'name')
                                        ->default(fn () => auth()->user()?->branchId())
                                        ->disabled(fn () => auth()->user()?->isBranchScoped())
                                        ->hint(fn () => auth()->user()?->isBranchScoped() ? 'Branch is auto-assigned from your profile.' : null)
                                        ->required(),

                                    Select::make('status')
                                        ->options([
                                            'Active' => 'Active',
                                            'Inactive' => 'Inactive',
                                            'Delinquent' => 'Delinquent',
                                        ])
                                        ->required(),
                                ])
                                ->columns(2),

                            Section::make('Employment')
                                ->schema([
                                    TextInput::make('employment_info'),
                                    TextInput::make('monthly_income'),
                                    TextInput::make('occupation'),
                                    TextInput::make('employer_name'),
                                    TextInput::make('monthly_income_range'),
                                ])
                                ->columns(3),

                            Section::make('Identification')
                                ->schema([
                                    TextInput::make('id_type')->label('ID Type'),
                                    TextInput::make('id_number')->label('ID Number'),
                                ]),

                            Section::make('Emergency Contact')
                                ->schema([
                                    TextInput::make('emergency_full_name')
                                        ->label('Fullname'),

                                 
                                    TextInput::make('emergency_phone')
                                        ->label('Phone')
                                        ->tel()
                                        ->required()
                                        ->maxLength(11)
                                        ->minLength(11)
                                        ->rule('digits:11')
                                        ->rule('regex:/^09\d{9}$/')
                                        ->extraInputAttributes(['maxlength' => 11])
                                        ->validationMessages([
                                            'required' => 'Phone number is required.',
                                            'digits' => 'Phone number must be exactly 11 digits.',
                                            'regex' => 'Phone number must start with 09 and be valid.',
                                        ])
                                        ->helperText('Enter exactly 11 digits (e.g., 09123456789)'),

                                    TextInput::make('emergency_relationship')
                                        ->label('Relationship'),
                                ]),

                            Section::make('Household Information')
                                ->schema([
                                    TextInput::make('years_in_coop')
                                        ->numeric()
                                        ->minValue(0),

                                    TextInput::make('dependents_count')
                                        ->label('No. of Dependents')
                                        ->numeric()
                                        ->minValue(0),

                                    TextInput::make('children_in_school_count')
                                        ->label('No. of Children in School')
                                        ->numeric()
                                        ->minValue(0),
                                ])
                                ->columns(3),
                        ]),

                    Step::make('Spouse & Co-Maker')
                        ->icon('heroicon-o-user')
                        ->completedIcon('heroicon-o-hand-thumb-up')
                        ->schema([
                            Section::make('Spouse Information')
                                ->relationship('spouse')
                                ->schema([
                                    TextInput::make('full_name')->label('Full Name'),

                                    Forms\Components\DatePicker::make('birthdate')
                                        ->label('Birthdate'),

                                    TextInput::make('occupation')->label('Occupation'),
                                    TextInput::make('employer_name')->label('Employer'),
                                    TextInput::make('employer_address')->label('Employer Address'),
                                    TextInput::make('source_of_income')->label('Source of Income'),
                                    TextInput::make('tin')->label('TIN'),
                                ])
                                ->columns(3)
                                ->visible(function (callable $get) {
                                    $profileId = $get('profile_id');

                                    if (! $profileId) {
                                        return false;
                                    }

                                    $profile = Profile::find($profileId);

                                    return strtolower($profile?->civil_status ?? '') === 'married';
                                }),

                            Section::make('Co-Makers / Guarantors')
                                ->schema([
                                    Repeater::make('coMakers')
                                        ->relationship()
                                        ->schema([
                                            TextInput::make('full_name')
                                                ->label('Full Name')
                                                ->required(),

                                            TextInput::make('relationship')
                                                ->label('Relationship'),

                                            TextInput::make('contact_number')
                                                ->label('Contact Number'),

                                            TextInput::make('address')
                                                ->label('Address')
                                                ->columnSpanFull(),

                                            TextInput::make('occupation')
                                                ->label('Occupation'),

                                            TextInput::make('employer_name')
                                                ->label('Employer'),

                                            TextInput::make('monthly_income')
                                                ->label('Monthly Income')
                                                ->numeric()
                                                ->prefix('₱'),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel('Add Co-Maker')
                                        ->defaultItems(0)
                                        ->reorderable(false),
                                ])
                                ->columnSpanFull(),
                        ]),
                ])
                    ->skippable()
                    ->columnSpanFull(),
            ]);
    }
}