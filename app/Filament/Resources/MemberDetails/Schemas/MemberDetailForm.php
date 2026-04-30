<?php

namespace App\Filament\Resources\MemberDetails\Schemas;

use App\Models\Profile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
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
                                        // Prevent switching profile on edit — doing so would orphan
                                        // all nested relationship data (address, spouse, co-makers).
                                        ->disabledOn('edit')
                                        // Must dehydrate even when disabled so the value survives
                                        // the save payload and isn't silently wiped.
                                        ->dehydratedWhenHidden()
                                        ->required(),

                                    Hidden::make('member_no'),

                                    Select::make('membership_type_id')
                                        ->label('Membership Type')
                                        ->relationship('membershipType', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),

                                    Select::make('status')
                                        ->options([
                                            'Active' => 'Active',
                                            'Dormant' => 'Dormant',
                                            'Inactive' => 'Inactive',
                                            'Delinquent' => 'Delinquent',
                                        ])
                                        ->required(),
                                ])
                                ->columns(2),

                            Section::make('Basic Information & Address')
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

                                    TextInput::make('mobile_number')
                                        ->label('Mobile Number')
                                        ->placeholder('09XXXXXXXXX')
                                        ->required()
                                        ->rules(['digits:11', 'regex:/^09\d{9}$/'])
                                        ->validationMessages([
                                            'digits' => 'Must be exactly 11 digits.',
                                            'regex' => 'Must start with 09 and be 11 digits.',
                                        ])
                                        ->extraInputAttributes([
                                            'inputmode' => 'numeric',
                                            'maxlength' => '11',
                                            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)",
                                        ]),

                                    DatePicker::make('birthdate')
                                        ->label('Birthdate')
                                        ->required()
                                        ->maxDate(now()->subYears(18))
                                        ->rule('before_or_equal:'.now()->subYears(18)->toDateString()),

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
                                        ->required()
                                        // ->live() broadcasts changes immediately so the Spouse
                                        // section visibility updates without a page reload.
                                        ->live(),
                                ])
                                ->columns(3),

                            // Address fields live on MemberDetail, not Profile — save them directly
                            Section::make('Address')
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

                            // Identification columns live on MemberDetail — no ->relationship()
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

                            // Employment columns live on MemberDetail — no ->relationship()
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
                                        ->label('Mobile Number')
                                        ->placeholder('09XXXXXXXXX')
                                        ->required()
                                        ->rules(['digits:11', 'regex:/^09\d{9}$/'])
                                        ->validationMessages([
                                            'digits' => 'Must be exactly 11 digits.',
                                            'regex' => 'Must start with 09 and be 11 digits.',
                                        ])
                                        ->extraInputAttributes([
                                            'inputmode' => 'numeric',
                                            'maxlength' => '11',
                                            'oninput' => "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)",
                                        ]),

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
                                ->description("Upload the front and back of the member's valid ID.")
                                ->schema([
                                    // ─────────────────────────────────────────────────────────
                                    // BUG — WHY PHOTOS HAD TO BE RE-UPLOADED ON EVERY EDIT:
                                    //
                                    // 1. Missing ->disk() / ->directory(): Without these, Filament
                                    //    cannot resolve the stored filename back to a real file path,
                                    //    so the field renders empty on the edit form.
                                    //
                                    // 2. Missing ->deletable(false): When a FileUpload field is
                                    //    empty and the user saves, Filament interprets that as
                                    //    "the user removed the file" and writes NULL to the column,
                                    //    deleting the stored path.
                                    //
                                    // 3. Unconditional ->required(): Even if the field pre-filled
                                    //    correctly, this forces validation to fail on every edit
                                    //    save unless a new file is chosen.
                                    //
                                    // FIX:
                                    //   • ->disk('public') + ->directory('member-ids') so Filament
                                    //     knows where the existing file lives and can pre-populate.
                                    //   • ->deletable(false) so an untouched field keeps its value.
                                    //   • ->required() only on create; edit skips the validation.
                                    //   • ->downloadable() + ->openable() so the current file is
                                    //     visible and accessible on the edit form.
                                    // ─────────────────────────────────────────────────────────
                                    FileUpload::make('id_document_front')
                                        ->label('ID Front')
                                        ->image()
                                        ->disk('public')
                                        ->directory('member-ids')
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
                                        ->maxSize(5120)
                                        ->deletable(false)
                                        ->downloadable()
                                        ->openable()
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->helperText('Front side of the ID (JPG, PNG, or PDF, max 5MB)'),

                                    FileUpload::make('id_document_back')
                                        ->label('ID Back')
                                        ->image()
                                        ->disk('public')
                                        ->directory('member-ids')
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
                                        ->maxSize(5120)
                                        ->deletable(false)
                                        ->downloadable()
                                        ->openable()
                                        ->required(fn (string $operation): bool => $operation === 'create')
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
                                // ─────────────────────────────────────────────────────────
                                // The original visibility only did a DB lookup via profile_id.
                                // This breaks on edit when profile_id is disabled (returns null)
                                // and also doesn't react to live civil_status changes in the form.
                                //
                                // FIX: Read `profile.civil_status` from Filament's live form state
                                // first (works on both create and edit, reflects unsaved changes).
                                // Fall back to a DB lookup only for the initial create page load
                                // before the user has interacted with the civil_status field.
                                // ─────────────────────────────────────────────────────────
                                ->visible(function (Get $get) {
                                    $civilStatus = $get('profile.civil_status');

                                    if (! $civilStatus) {
                                        $profileId = $get('profile_id');
                                        if (! $profileId) {
                                            return false;
                                        }
                                        $civilStatus = Profile::find($profileId)?->civil_status;
                                    }

                                    return strtolower($civilStatus ?? '') === 'married';
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
                    ->persistStepInQueryString('step')
                    ->columnSpanFull(),
            ]);
    }
}
