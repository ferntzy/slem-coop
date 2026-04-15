<?php

namespace App\Filament\Resources\LoanApplications\Schemas;

use App\Models\LoanType;
use App\Models\MemberDetail;
use App\Services\CoopFeeCalculatorService;
use Carbon\Carbon;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LoanApplicationsForm
{
    protected static function getMember(callable $get): ?MemberDetail
    {
        $memberId = $get('member_id');

        if (! $memberId) {
            return null;
        }

        return MemberDetail::with([
            'profile',
            'membershipType',
            'branch',
            'spouse',
            'coMakers',
        ])->find($memberId);
    }

    protected static function getLoanType(callable $get): ?LoanType
    {
        $loanTypeId = $get('loan_type_id');

        if (! $loanTypeId) {
            return null;
        }

        return LoanType::find($loanTypeId);
    }

    protected static function money(?float $amount): string
    {
        return $amount !== null ? '₱'.number_format($amount, 2) : '—';
    }

    protected static function getInterestRateDisplay(?LoanType $type): ?string
    {
        if (! $type || blank($type->max_interest_rate)) {
            return null;
        }

        return rtrim(rtrim((string) $type->max_interest_rate, '0'), '.').'%';
    }

    protected static function requiresCollateral(?LoanType $type, float $amount): bool
    {
        if (! $type || ! $type->requires_collateral) {
            return false;
        }

        $threshold = (float) ($type->collateral_threshold ?? 0);

        return $amount > $threshold;
    }

    protected static function getCollateralThresholdLabel(?LoanType $type): string
    {
        $threshold = (float) ($type?->collateral_threshold ?? 0);

        return '₱'.number_format($threshold, 2);
    }

    protected static function applyDerivedLoanFields(callable $set, ?LoanType $type, float $amount): void
    {
        $fees = app(CoopFeeCalculatorService::class)
            ->calculate('loan_application', $amount);

        $set('interest_rate_display', static::getInterestRateDisplay($type));
        $set(
            'collateral_status',
            static::requiresCollateral($type, $amount)
                ? 'Pending Verification'
                : 'Not Required'
        );

        $set('coop_fee_total', $fees['coop_fee_total'] ?? 0);
        $set('net_release_amount', $fees['net_release_amount'] ?? 0);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Loan Application Details')
                        ->schema([
                            Section::make('Loan Application Details')
                                ->schema([
                                    Select::make('member_id')
                                        ->label('Member')
                                        ->searchable()
                                        ->getSearchResultsUsing(function (string $search) {
                                            return MemberDetail::with('profile')
                                                ->where('status', 'Active')
                                                ->where(function (Builder $query) use ($search) {
                                                    $query->where('member_no', 'like', "%{$search}%")
                                                        ->orWhereHas('profile', function (Builder $query) use ($search) {
                                                            $query->where('first_name', 'like', "%{$search}%")
                                                                ->orWhere('middle_name', 'like', "%{$search}%")
                                                                ->orWhere('last_name', 'like', "%{$search}%")
                                                                ->orWhere('email', 'like', "%{$search}%");
                                                        });
                                                })
                                                ->limit(50)
                                                ->get()
                                                ->mapWithKeys(fn ($member) => [
                                                    $member->id => $member->profile->full_name.' — '.$member->member_no,
                                                ]);
                                        })
                                        ->getOptionLabelUsing(fn ($value) => optional(MemberDetail::with('profile')->find($value))
                                            ?->profile->full_name.' — '.
                                            optional(MemberDetail::find($value))->member_no
                                        )
                                        ->reactive()
                                        ->required(fn () => ! Auth::user()?->isMember())
                                        ->visible(fn () => ! Auth::user()?->isMember()),

                                    Hidden::make('member_id')
                                        ->default(function () {
                                            $user = Auth::user();

                                            if (! $user) {
                                                return null;
                                            }

                                            return MemberDetail::where('profile_id', $user->profile_id)->value('id');
                                        })
                                        ->reactive()
                                        ->required(fn () => Auth::user()?->isMember())
                                        ->visible(fn () => Auth::user()?->isMember()),

                                    Placeholder::make('member_display')
                                        ->label('Member')
                                        ->content(function () {
                                            $user = Auth::user();

                                            if (! $user) {
                                                return 'No logged-in user';
                                            }

                                            $member = MemberDetail::with('profile')
                                                ->where('profile_id', $user->profile_id)
                                                ->first();

                                            if (! $member) {
                                                return 'No member record found';
                                            }

                                            return $member->profile->full_name.' — '.$member->member_no;
                                        })
                                        ->visible(fn () => Auth::user()?->isMember()),

                                    Select::make('loan_type_id')
                                        ->label('Loan Type')
                                        ->relationship(
                                            'type',
                                            'name',
                                            fn ($query) => $query->where('is_active', true)
                                        )
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $type = LoanType::find($state);
                                            $amount = (float) ($get('amount_requested') ?: 0);

                                            static::applyDerivedLoanFields($set, $type, $amount);
                                        })
                                        ->required(),

                                    TextInput::make('amount_requested')
                                        ->label('Amount Requested')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->live(onBlur: true)
                                        ->rules(function (callable $get) {
                                            $type = static::getLoanType($get);

                                            if (! $type) {
                                                return [];
                                            }

                                            $rules = [];

                                            if (! is_null($type->min_amount)) {
                                                $rules[] = "min:{$type->min_amount}";
                                            }

                                            if (! is_null($type->max_amount)) {
                                                $rules[] = "max:{$type->max_amount}";
                                            }

                                            return $rules;
                                        })
                                        ->helperText(function (callable $get) {
                                            $type = static::getLoanType($get);

                                            if (! $type) {
                                                return null;
                                            }

                                            return "Allowed: ₱{$type->min_amount} - ₱{$type->max_amount}";
                                        })
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                            $amount = (float) ($state ?: 0);
                                            $type = static::getLoanType($get);

                                            static::applyDerivedLoanFields($set, $type, $amount);
                                        })
                                        ->maxValue(function (callable $get) {
                                            return static::getLoanType($get)?->max_amount;
                                        })
                                        // ->required(),
                                        ->nullable(),

                                    TextInput::make('coop_fee_total')
                                        ->label('Total Coop Fee')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->readOnly()
                                        ->dehydrated(false)
                                        ->default(0),

                                    TextInput::make('net_release_amount')
                                        ->label('Net Release Amount')
                                        ->numeric()
                                        ->prefix('₱')
                                        ->readOnly()
                                        ->dehydrated(false)
                                        ->default(0),

                                    TextInput::make('term_months')
                                        ->label('Term (Months)')
                                        ->numeric()
                                        ->required()
                                        ->rules(function (callable $get) {
                                            $type = static::getLoanType($get);

                                            if (! $type) {
                                                return [];
                                            }

                                            return [
                                                'min:1',
                                                "max:{$type->max_term_months}",
                                            ];
                                        })
                                        ->helperText(function (callable $get) {
                                            $type = static::getLoanType($get);

                                            if (! $type) {
                                                return null;
                                            }

                                            return "Max term: {$type->max_term_months} months";
                                        }),

                                    TextInput::make('interest_rate_display')
                                        ->label('Interest Rate')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->reactive(),

                                    TextInput::make('status')
                                        ->label('Loan Status')
                                        ->default('Pending')
                                        ->disabled()
                                        ->dehydrated(true),
                                ])
                                ->columns(2),

                            Section::make('Basic Information')
                                ->schema([
                                    Placeholder::make('member_full_name')
                                        ->label('Full Name')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->full_name ?? '—'),

                                    Placeholder::make('member_first_name')
                                        ->label('First Name')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->first_name ?? '—'),

                                    Placeholder::make('member_middle_name')
                                        ->label('Middle Name')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->middle_name ?? '—'),

                                    Placeholder::make('member_last_name')
                                        ->label('Last Name')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->last_name ?? '—'),

                                    Placeholder::make('birthdate_display')
                                        ->label('Birthdate')
                                        ->content(function (callable $get) {
                                            $birthdate = static::getMember($get)?->profile?->birthdate;

                                            return $birthdate ? Carbon::parse($birthdate)->format('F j, Y') : '—';
                                        }),

                                    Placeholder::make('age_display')
                                        ->label('Age')
                                        ->content(function (callable $get) {
                                            $birthdate = static::getMember($get)?->profile?->birthdate;

                                            return $birthdate ? Carbon::parse($birthdate)->age : '—';
                                        }),

                                    Placeholder::make('sex_display')
                                        ->label('Sex')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->sex ?? '—'),

                                    Placeholder::make('civil_status_display')
                                        ->label('Civil Status')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->civil_status ?? '—'),

                                    Placeholder::make('tin_display')
                                        ->label('TIN')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->tin ?? '—'),
                                ])
                                ->columns(3),

                            Section::make('Contact Information')
                                ->schema([
                                    Placeholder::make('mobile_number_display')
                                        ->label('Mobile Number')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->mobile_number ?? '—'),

                                    Placeholder::make('email_display')
                                        ->label('Email')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->email ?? '—'),

                                    Placeholder::make('address_display')
                                        ->label('Address')
                                        ->content(fn (callable $get) => static::getMember($get)?->profile?->address ?? '—'),
                                ])
                                ->columns(3),

                            Section::make('Membership Information')
                                ->schema([
                                    Placeholder::make('member_no_display')
                                        ->label('Member No.')
                                        ->content(fn (callable $get) => static::getMember($get)?->member_no ?? '—'),

                                    Placeholder::make('membership_type_display')
                                        ->label('Membership Type')
                                        ->content(fn (callable $get) => static::getMember($get)?->membershipType?->name ?? '—'),

                                    Placeholder::make('branch_display')
                                        ->label('Branch')
                                        ->content(fn (callable $get) => static::getMember($get)?->branch?->name ?? '—'),

                                    Placeholder::make('member_status_display')
                                        ->label('Member Status')
                                        ->content(fn (callable $get) => static::getMember($get)?->status ?? '—'),

                                    Placeholder::make('years_in_coop_display')
                                        ->label('Years in Coop')
                                        ->content(fn (callable $get) => static::getMember($get)?->years_in_coop ?? '—'),

                                    Placeholder::make('dependents_count_display')
                                        ->label('No. of Dependents')
                                        ->content(fn (callable $get) => static::getMember($get)?->dependents_count ?? '—'),

                                    Placeholder::make('children_in_school_count_display')
                                        ->label('No. of Children in School')
                                        ->content(fn (callable $get) => static::getMember($get)?->children_in_school_count ?? '—'),
                                ])
                                ->columns(3),

                            Section::make('Employment and Identification')
                                ->schema([
                                    Placeholder::make('occupation_display')
                                        ->label('Occupation')
                                        ->content(fn (callable $get) => static::getMember($get)?->occupation ?? '—'),

                                    Placeholder::make('employer_display')
                                        ->label('Employer')
                                        ->content(fn (callable $get) => static::getMember($get)?->employer_name ?? '—'),

                                    Placeholder::make('employment_info_display')
                                        ->label('Employment Info')
                                        ->content(fn (callable $get) => static::getMember($get)?->employment_info ?? '—'),

                                    Placeholder::make('monthly_income_display')
                                        ->label('Monthly Income')
                                        ->content(fn (callable $get) => static::money(static::getMember($get)?->monthly_income)),

                                    Placeholder::make('monthly_income_range_display')
                                        ->label('Monthly Income Range')
                                        ->content(fn (callable $get) => static::getMember($get)?->monthly_income_range ?? '—'),

                                    Placeholder::make('id_type_display')
                                        ->label('ID Type')
                                        ->content(fn (callable $get) => static::getMember($get)?->id_type ?? '—'),

                                    Placeholder::make('id_number_display')
                                        ->label('ID Number')
                                        ->content(fn (callable $get) => static::getMember($get)?->id_number ?? '—'),
                                ])
                                ->columns(3),

                            Section::make('Emergency Contact')
                                ->schema([
                                    Placeholder::make('emergency_full_name_display')
                                        ->label('Full Name')
                                        ->content(fn (callable $get) => static::getMember($get)?->emergency_full_name ?? '—'),

                                    Placeholder::make('emergency_phone_display')
                                        ->label('Phone')
                                        ->content(fn (callable $get) => static::getMember($get)?->emergency_phone ?? '—'),

                                    Placeholder::make('emergency_relationship_display')
                                        ->label('Relationship')
                                        ->content(fn (callable $get) => static::getMember($get)?->emergency_relationship ?? '—'),
                                ])
                                ->columns(3),

                            Section::make('Spouse Information')
                                ->schema([
                                    Placeholder::make('spouse_name')
                                        ->label('Full Name')
                                        ->content(fn (callable $get) => static::getMember($get)?->spouse?->full_name ?? '—'),

                                    Placeholder::make('spouse_birthdate')
                                        ->label('Birthdate')
                                        ->content(function (callable $get) {
                                            $birthdate = static::getMember($get)?->spouse?->birthdate;

                                            return $birthdate ? Carbon::parse($birthdate)->format('F j, Y') : '—';
                                        }),

                                    Placeholder::make('spouse_occupation')
                                        ->label('Occupation')
                                        ->content(fn (callable $get) => static::getMember($get)?->spouse?->occupation ?? '—'),

                                    Placeholder::make('spouse_employer')
                                        ->label('Employer')
                                        ->content(fn (callable $get) => static::getMember($get)?->spouse?->employer_name ?? '—'),

                                    Placeholder::make('spouse_employer_address')
                                        ->label('Employer Address')
                                        ->content(fn (callable $get) => static::getMember($get)?->spouse?->employer_address ?? '—'),

                                    Placeholder::make('spouse_income_source')
                                        ->label('Source of Income')
                                        ->content(fn (callable $get) => static::getMember($get)?->spouse?->source_of_income ?? '—'),

                                    Placeholder::make('spouse_tin')
                                        ->label('TIN')
                                        ->content(fn (callable $get) => static::getMember($get)?->spouse?->tin ?? '—'),
                                ])
                                ->columns(3)
                                ->visible(function (callable $get) {
                                    $civilStatus = strtolower((string) (static::getMember($get)?->profile?->civil_status ?? ''));

                                    return $civilStatus === 'married';
                                }),

                            Section::make('Co-Makers / Guarantors')
                                ->schema([
                                    Grid::make(1)
                                        ->schema(function (callable $get) {
                                            $member = static::getMember($get);

                                            if (! $member || $member->coMakers->isEmpty()) {
                                                return [
                                                    Placeholder::make('no_co_makers')
                                                        ->label('')
                                                        ->content('No co-makers added.'),
                                                ];
                                            }

                                            return $member->coMakers->map(function ($coMaker, $index) {
                                                return Section::make('Co-Maker #'.($index + 1))
                                                    ->schema([
                                                        Placeholder::make("co_maker_name_{$index}")
                                                            ->label('Full Name')
                                                            ->content($coMaker->full_name ?? '—'),

                                                        Placeholder::make("co_maker_relationship_{$index}")
                                                            ->label('Relationship')
                                                            ->content($coMaker->relationship ?? '—'),

                                                        Placeholder::make("co_maker_contact_{$index}")
                                                            ->label('Contact Number')
                                                            ->content($coMaker->contact_number ?? '—'),

                                                        Placeholder::make("co_maker_occupation_{$index}")
                                                            ->label('Occupation')
                                                            ->content($coMaker->occupation ?? '—'),

                                                        Placeholder::make("co_maker_employer_{$index}")
                                                            ->label('Employer')
                                                            ->content($coMaker->employer_name ?? '—'),

                                                        Placeholder::make("co_maker_income_{$index}")
                                                            ->label('Monthly Income')
                                                            ->content(
                                                                $coMaker->monthly_income !== null
                                                                    ? '₱'.number_format($coMaker->monthly_income, 2)
                                                                    : '—'
                                                            ),

                                                        Placeholder::make("co_maker_address_{$index}")
                                                            ->label('Address')
                                                            ->content($coMaker->address ?? '—')
                                                            ->columnSpanFull(),
                                                    ])
                                                    ->columns(3)
                                                    ->collapsible();
                                            })->toArray();
                                        }),
                                ])
                                ->columnSpanFull()
                                ->visible(fn (callable $get) => filled($get('member_id'))),
                        ]),

                    Step::make('Cash Flow Form')
                        ->schema([
                            Section::make('Income')
                                ->description('Enter monthly income sources and upload supporting documents.')
                                ->schema([
                                    TextInput::make('salary')->label('Salary')->numeric()->prefix('₱')->default(0),
                                    TextInput::make('business_income')->label('Business Income')->numeric()->prefix('₱')->default(0),
                                    TextInput::make('remittances')->label('Remittances')->numeric()->prefix('₱')->default(0),
                                    TextInput::make('other_income')->label('Other Income')->numeric()->prefix('₱')->default(0),
                                ])
                                ->columns(2),

                            Section::make('Expenses')
                                ->description('Enter monthly expenses and upload supporting documents if available.')
                                ->schema([
                                    TextInput::make('living_expenses')->label('Living Expenses')->numeric()->prefix('₱')->default(0),
                                    TextInput::make('business_expenses')->label('Business Expenses')->numeric()->prefix('₱')->default(0),
                                    TextInput::make('existing_loan_payments')->label('Existing Loan Payments')->numeric()->prefix('₱')->default(0),
                                    TextInput::make('other_expenses')->label('Other Expenses')->numeric()->prefix('₱')->default(0),
                                ])
                                ->columns(2),

                            FileUpload::make('cashflow_documents')
                                ->label('Cash Flow Supporting Documents')
                                ->disk('public_storage_folder')
                                ->visibility('public')
                                ->directory('loan-cashflow-evidence')
                                ->multiple()
                                ->reorderable()
                                ->openable()
                                ->downloadable()
                                ->acceptedFileTypes([
                                    'application/pdf',
                                    'image/jpeg',
                                    'image/png',
                                ])
                                ->maxFiles(20)
                                ->maxSize(5120)
                                ->helperText('Upload ALL supporting documents (income + expenses): payslips, receipts, bank statements, etc.')
                                ->columnSpanFull(),

                            Section::make('Loan Collateral')
                                ->description(function (callable $get) {
                                    $type = static::getLoanType($get);

                                    if (! $type?->requires_collateral) {
                                        return 'Collateral verification is not required for this loan type.';
                                    }

                                    return 'Collateral verification is required when the loan amount exceeds '.static::getCollateralThresholdLabel($type).'.';
                                })
                                ->schema([
                                    Placeholder::make('collateral_warning')
                                        ->label('')
                                        ->content(function (callable $get) {
                                            $type = static::getLoanType($get);

                                            if (! $type?->requires_collateral) {
                                                return 'Collateral is not required for this loan type.';
                                            }

                                            return '⚠ Collateral is required when the loan amount exceeds '.static::getCollateralThresholdLabel($type).'. Please upload supporting documents.';
                                        })
                                        ->extraAttributes([
                                            'class' => 'p-4 rounded-lg bg-yellow-50 border border-yellow-400 text-yellow-800 font-semibold',
                                        ]),

                                    Select::make('collateral_type')
                                        ->label('Collateral Type')
                                        ->options([
                                            'land_title' => 'Land Title',
                                            'vehicle' => 'Vehicle OR/CR',
                                            'appliance' => 'Appliance',
                                            'guarantor' => 'Guarantor',
                                        ])
                                        ->required(function (callable $get) {
                                            $type = static::getLoanType($get);
                                            $amount = (float) ($get('amount_requested') ?? 0);

                                            return static::requiresCollateral($type, $amount);
                                        }),

                                    FileUpload::make('collateral_document')
                                        ->label('Collateral Document')
                                        ->disk('public_storage_folder')
                                        ->visibility('public')
                                        ->directory('loan-collaterals')
                                        ->acceptedFileTypes([
                                            'application/pdf',
                                            'image/jpeg',
                                            'image/png',
                                        ])
                                        ->maxSize(5120)
                                        ->helperText('Upload land title, vehicle OR/CR, or other collateral proof.')
                                        ->required(function (callable $get) {
                                            $type = static::getLoanType($get);
                                            $amount = (float) ($get('amount_requested') ?? 0);

                                            return static::requiresCollateral($type, $amount);
                                        })
                                        ->openable()
                                        ->downloadable(),

                                    Select::make('collateral_status')
                                        ->label('Collateral Status')
                                        ->options([
                                            'Not Required' => 'Not Required',
                                            'Pending Verification' => 'Pending Verification',
                                            'Approved' => 'Approved',
                                            'Rejected' => 'Rejected',
                                        ])
                                        ->default('Not Required')
                                        ->disabled()
                                        ->dehydrated(true),
                                ])
                                ->columns(2)
                                ->visible(function (callable $get) {
                                    $type = static::getLoanType($get);
                                    $amount = (float) ($get('amount_requested') ?? 0);

                                    return static::requiresCollateral($type, $amount);
                                }),
                        ])
                        ->columnSpanFull(),

                    Step::make('Internal Review')
                        ->schema([
                            Section::make('Internal Review')
                                ->description('For officer/manager review and background investigation findings.')
                                ->schema([
                                    Textarea::make('evaluation_notes')
                                        ->label('Evaluation Notes')
                                        ->rows(4)
                                        ->placeholder('Summarize repayment capacity, document completeness, loan purpose, and recommendation.')
                                        ->helperText('Internal assessment by the loan officer or evaluator.')
                                        ->columnSpanFull()
                                        ->visible(fn (string $operation) => $operation !== 'create')
                                        ->disabled(fn ($record) => ! in_array($record?->status, ['Under Review', 'Approved', 'Rejected'], true)),

                                    Textarea::make('bici_notes')
                                        ->label('BI/CI Notes')
                                        ->rows(5)
                                        ->placeholder('Write background / credit investigation findings such as neighbor feedback, reputation, payment behavior, and trustworthiness.')
                                        ->helperText('Background Investigation / Credit Investigation notes.')
                                        ->columnSpanFull()
                                        ->visible(fn (string $operation) => $operation !== 'create')
                                        ->disabled(fn ($record) => ! in_array($record?->status, ['Under Review', 'Approved', 'Rejected'], true)),
                                ])
                                ->columns(1),
                        ])
                        ->visible(fn (string $operation) => $operation !== 'create'
                            && auth()->check()
                            && auth()->user()->hasAnyRole(['super_admin', 'Admin', 'Staff', 'Teller', 'Manager'])
                        )

                        ->columnSpanFull(),
                ])
                    ->skippable()
                    ->columnSpanFull(),
            ]);
    }
}
