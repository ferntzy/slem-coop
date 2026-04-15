<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use App\Models\Profile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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

                    // ─────────────────────────────────────────
                    // STEP 1 — Personal Details
                    // ─────────────────────────────────────────
                    Step::make('Personal Details')
                        ->icon('heroicon-o-identification')
                        ->completedIcon('heroicon-o-hand-thumb-up')
                        ->schema([

                            Section::make('Member & Membership Info')
                                ->schema([
                                    Select::make('profile_id')
                                        ->label('Profile')
                                        ->relationship('profile', 'email')
                                        ->searchable()
                                        ->preload()
                                        ->getOptionLabelFromRecordUsing(
                                            fn ($record) => $record->full_name.' — '.$record->email
                                        )
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
                                        ->disabled(fn () => auth()->user()?->isStaff())
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

                            Section::make('Basic Information')
                                ->relationship('profile')
                                ->schema([
                                    TextInput::make('first_name')
                                        ->label('First Name')
                                        ->required(),

                                    TextInput::make('middle_name')
                                        ->label('Middle Name'),

                                    TextInput::make('last_name')
                                        ->label('Last Name')
                                        ->required(),

                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->required(),

                                    TextInput::make('mobile_number')
                                        ->label('Mobile Number')
                                        ->tel()
                                        ->placeholder('09XXXXXXXXX')
                                        ->maxLength(11),

                                    DatePicker::make('birthdate')
                                        ->label('Birthdate')
                                        ->required(),

                                    Select::make('sex')
                                        ->label('Sex')
                                        ->options([
                                            'Male' => 'Male',
                                            'Female' => 'Female',
                                        ])
                                        ->required(),

                                    Select::make('civil_status')
                                        ->label('Civil Status')
                                        ->options([
                                            'Single' => 'Single',
                                            'Married' => 'Married',
                                            'Widowed' => 'Widowed',
                                            'Separated' => 'Separated',
                                            'Annulled' => 'Annulled',
                                        ])
                                        ->required(),
                                ])
                                ->columns(3),

                            Section::make('Identification')
                                ->schema([
                                    Select::make('id_type')
                                        ->label('ID Type')
                                        ->options([
                                            'TIN' => 'TIN',
                                            'Philippine National ID (PhilSys ID)' => 'Philippine National ID (PhilSys ID)',
                                            'Passport' => 'Passport',
                                            "Driver's License" => "Driver's License",
                                            'UMID (SSS/GSIS ID)' => 'UMID (SSS/GSIS ID)',
                                            'PRC ID (for licensed professionals)' => 'PRC ID (for licensed professionals)',
                                            "Voter's ID (if still available)" => "Voter's ID (if still available)",
                                            'Postal ID' => 'Postal ID',
                                            'Senior Citizen ID' => 'Senior Citizen ID',
                                            'PWD ID' => 'PWD ID',
                                        ])
                                        ->searchable()
                                        ->required(),

                                    TextInput::make('id_number')
                                        ->label('ID Number')
                                        ->required(),
                                ])
                                ->columns(2),

                            Section::make('Address')
                                ->relationship('profile')
                                ->schema([
                                    TextInput::make('house_no')
                                        ->label('House No.')
                                        ->required(),

                                    TextInput::make('street_barangay')
                                        ->label('Street / Barangay')
                                        ->required(),

                                    TextInput::make('municipality')
                                        ->label('Municipality / City')
                                        ->required(),

                                    TextInput::make('province')
                                        ->label('Province')
                                        ->required(),

                                    TextInput::make('zip_code')
                                        ->label('Zip Code')
                                        ->maxLength(4)
                                        ->required(),
                                ])
                                ->columns(3),

                            Section::make('Employment & Income')
                                ->schema([
                                    TextInput::make('occupation')
                                        ->required(),

                                    TextInput::make('employer_name')
                                        ->label('Employer / Business Name'),

                                    Select::make('source_of_income')
                                        ->label('Source of Income')
                                        ->options([
                                            'Employment' => 'Employment',
                                            'Business' => 'Business',
                                            'Remittance' => 'Remittance',
                                            'Pension/Retirement' => 'Pension/Retirement',
                                            'Agriculture' => 'Agriculture',
                                            'Others' => 'Others',
                                        ])
                                        ->required()
                                        ->live(),

                                    Select::make('monthly_income_range')
                                        ->label('Monthly Income Range')
                                        ->options([
                                            'Below ₱10,000' => 'Below ₱10,000',
                                            '₱10,000 – ₱20,000' => '₱10,000 – ₱20,000',
                                            '₱20,001 – ₱30,000' => '₱20,001 – ₱30,000',
                                            '₱30,001 – ₱50,000' => '₱30,001 – ₱50,000',
                                            '₱50,001 – ₱100,000' => '₱50,001 – ₱100,000',
                                            'Above ₱100,000' => 'Above ₱100,000',
                                        ])
                                        ->required(),

                                    TextInput::make('monthly_income')
                                        ->label('Monthly Income (₱)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix('₱')
                                        ->required(),

                                    TextInput::make('years_in_business')
                                        ->label('Years in Business')
                                        ->numeric()
                                        ->minValue(0)
                                        ->visible(fn (Get $get) => $get('source_of_income') === 'Business'),
                                ])
                                ->columns(3),

                            Section::make('Household Information')
                                ->schema([
                                    TextInput::make('years_in_coop')
                                        ->label('Years in Coop')
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

                            Section::make('Emergency Contact')
                                ->schema([
                                    TextInput::make('emergency_full_name')
                                        ->label('Full Name')
                                        ->required(),

                                    TextInput::make('emergency_phone')
                                        ->label('Phone Number')
                                        ->tel()
                                        ->placeholder('09XXXXXXXXX')
                                        ->maxLength(11)
                                        ->required(),

                                    TextInput::make('emergency_relationship')
                                        ->label('Relationship')
                                        ->required(),
                                ])
                                ->columns(3),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 2 — Application & Documents
                    // ─────────────────────────────────────────
                    Step::make('Application & Documents')
                        ->icon('heroicon-o-document-text')
                        ->completedIcon('heroicon-o-hand-thumb-up')
                        ->schema([

                            Section::make('Application Details')
                                ->schema([
                                    DatePicker::make('application_date')
                                        ->label('Application Date')
                                        ->default(now())
                                        ->required(),

                                    Textarea::make('remarks')
                                        ->label('Remarks')
                                        ->placeholder('Any additional notes...')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),

                            Section::make('ID Documents')
                                ->description('Upload the front and back of the member\'s valid ID.')
                                ->schema([
                                    FileUpload::make('id_document_front')
                                        ->label('ID Front')
                                        ->image()
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
                                        ->maxSize(5120)
                                        ->required()
                                        ->helperText('Front side of the ID (JPG, PNG, or PDF, max 5MB)'),

                                    FileUpload::make('id_document_back')
                                        ->label('ID Back')
                                        ->image()
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
                                        ->maxSize(5120)
                                        ->required()
                                        ->helperText('Back side of the ID (JPG, PNG, or PDF, max 5MB)'),
                                ])
                                ->columns(2),
                        ]),

                    // ─────────────────────────────────────────
                    // STEP 3 — Spouse & Co-Makers
                    // ─────────────────────────────────────────
                    Step::make('Spouse & Co-Maker')
                        ->icon('heroicon-o-user')
                        ->completedIcon('heroicon-o-hand-thumb-up')
                        ->schema([

                            Section::make('Spouse Information')
                                ->relationship('spouse')
                                ->schema([
                                    TextInput::make('full_name')
                                        ->label('Full Name'),

                                    DatePicker::make('birthdate')
                                        ->label('Birthdate'),

                                    TextInput::make('occupation')
                                        ->label('Occupation'),

                                    TextInput::make('employer_name')
                                        ->label('Employer'),

                                    TextInput::make('employer_address')
                                        ->label('Employer Address'),

                                    Select::make('source_of_income')
                                        ->label('Source of Income')
                                        ->options([
                                            'Employment' => 'Employment',
                                            'Business' => 'Business',
                                            'Remittance' => 'Remittance',
                                            'Pension/Retirement' => 'Pension/Retirement',
                                            'Agriculture' => 'Agriculture',
                                            'Others' => 'Others',
                                        ]),

                                    TextInput::make('monthly_income')
                                        ->label('Monthly Income (₱)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->prefix('₱'),

                                    TextInput::make('tin')
                                        ->label('TIN'),
                                ])
                                ->columns(3)
                                ->visible(function (Get $get) {
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
                                                ->label('Contact Number')
                                                ->tel(),

                                            TextInput::make('address')
                                                ->label('Address')
                                                ->columnSpanFull(),

                                            TextInput::make('occupation')
                                                ->label('Occupation'),

                                            TextInput::make('employer_name')
                                                ->label('Employer'),

                                            TextInput::make('monthly_income')
                                                ->label('Monthly Income (₱)')
                                                ->numeric()
                                                ->minValue(0)
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
