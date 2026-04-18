<?php

namespace App\Filament\Resources\MembershipApplications\Schemas;

use App\Models\MemberDetail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class MembershipApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('tabs')
                    ->tabs([
                        Tabs\Tab::make('applicant')
                            ->icon('heroicon-o-user-circle')
                            ->label('Applicant Information')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label('First Name')
                                            ->required()
                                            ->maxLength(100),

                                        TextInput::make('middle_name')
                                            ->label('Middle Name')
                                            ->maxLength(45),

                                        TextInput::make('last_name')
                                            ->label('Last Name')
                                            ->required()
                                            ->maxLength(45),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->required(),

                                        TextInput::make('mobile_number')
                                            ->label('Mobile Number')
                                            ->placeholder('09XXXXXXXXX')
                                            ->length(11)
                                            ->regex('/^09\d{9}$/')
                                            ->validationMessages([
                                                'regex' => 'Mobile number must be in PH format (09XXXXXXXXX)',
                                            ])
                                            ->extraInputAttributes([
                                                'inputmode' => 'numeric',
                                                'pattern' => '09[0-9]{9}',
                                                'x-on:keypress' => 'if(!/[0-9]/.test($event.key)) $event.preventDefault()',
                                                'x-on:input' => '$event.target.value = $event.target.value.replace(/[^0-9]/g, "").slice(0, 11)',
                                                'x-on:paste' => '$event.preventDefault()',
                                            ])
                                            ->helperText('Format: 09123456789 (11 digits)')
                                            ->required(),

                                        DatePicker::make('birthdate')
                                            ->label('Birthdate')
                                            ->required(),

                                        Select::make('sex')
                                            ->label('Sex')
                                            ->options([
                                                'Male' => 'Male',
                                                'Female' => 'Female',
                                            ])
                                            ->nullable(),

                                        Select::make('civil_status')
                                            ->label('Civil Status')
                                            ->options([
                                                'Single' => 'Single',
                                                'Married' => 'Married',
                                                'Widowed' => 'Widowed',
                                                'Separated' => 'Separated',
                                                'Annulled' => 'Annulled',
                                            ])
                                            ->nullable(),

                                        TextInput::make('occupation')
                                            ->label('Occupation')
                                            ->maxLength(100),

                                        TextInput::make('employer_name')
                                            ->label('Employer Name')
                                            ->maxLength(100),

                                        TextInput::make('monthly_income')
                                            ->label('Monthly Income')
                                            ->numeric()
                                            ->prefix('₱'),

                                        TextInput::make('source_of_income')
                                            ->label('Source of Income')
                                            ->maxLength(100),

                                        Textarea::make('address')
                                            ->label('Address')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('orientation')
                            ->icon('heroicon-o-academic-cap')
                            ->label('Orientation Status')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Placeholder::make('orientation_score')
                                            ->label('Assessment Score')
                                            ->content(function ($record) {
                                                if (! $record?->orientation_score) {
                                                    return new HtmlString('<span class="text-gray-400 font-semibold">N/A</span>');
                                                }
                                                $score = $record->orientation_score;
                                                $color = $score >= 75 ? 'text-green-600' : 'text-red-600';

                                                return new HtmlString("<span class='text-2xl font-bold {$color}'>{$score}%</span>");
                                            }),

                                        Placeholder::make('orientation_zoom_attended')
                                            ->label('Zoom Attended')
                                            ->content(function ($record) {
                                                if ($record?->orientation_zoom_attended) {
                                                    return new HtmlString('<span class="inline-flex items-center gap-1 text-green-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Yes</span>');
                                                }

                                                return new HtmlString('<span class="inline-flex items-center gap-1 text-red-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> No</span>');
                                            }),

                                        Placeholder::make('orientation_video_completed')
                                            ->label('Video Completed')
                                            ->content(function ($record) {
                                                if ($record?->orientation_video_completed) {
                                                    return new HtmlString('<span class="inline-flex items-center gap-1 text-green-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Yes</span>');
                                                }

                                                return new HtmlString('<span class="inline-flex items-center gap-1 text-red-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> No</span>');
                                            }),

                                        Placeholder::make('orientation_assessment_passed')
                                            ->label('Assessment Passed')
                                            ->content(function ($record) {
                                                if ($record?->orientation_assessment_passed) {
                                                    return new HtmlString('<span class="inline-flex items-center gap-1 text-green-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Yes</span>');
                                                }

                                                return new HtmlString('<span class="inline-flex items-center gap-1 text-red-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> No</span>');
                                            }),

                                        Placeholder::make('orientation_certificate_generated')
                                            ->label('Certificate Generated')
                                            ->content(function ($record) {
                                                if ($record?->orientation_certificate_generated) {
                                                    return new HtmlString('<span class="inline-flex items-center gap-1 text-green-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Yes</span>');
                                                }

                                                return new HtmlString('<span class="inline-flex items-center gap-1 text-red-600 font-semibold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> No</span>');
                                            }),

                                        Placeholder::make('orientation_complete')
                                            ->label('Orientation Complete')
                                            ->content(function ($record) {
                                                if (! $record) {
                                                    return new HtmlString('<span class="text-gray-400 font-semibold">N/A</span>');
                                                }
                                                $complete = $record->orientation_zoom_attended &&
                                                           $record->orientation_video_completed &&
                                                           $record->orientation_assessment_passed;
                                                if ($complete) {
                                                    return new HtmlString('<span class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-100 text-green-700 rounded-full font-bold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Complete</span>');
                                                }

                                                return new HtmlString('<span class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-100 text-amber-700 rounded-full font-bold"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> Incomplete</span>');
                                            }),
                                    ]),
                            ]),

                        Tabs\Tab::make('details')
                            ->icon('heroicon-o-clipboard-document-check')
                            ->label('Application Details')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('membership_type_id')
                                            ->label('Membership Type')
                                            ->relationship('membershipType', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        TextInput::make('applied_branch')
                                            ->label('Applied Branch')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->formatStateUsing(function ($state, $record): string {
                                                $branchName = $record?->profile?->memberDetail?->branch?->name;

                                                if ($branchName) {
                                                    return $branchName;
                                                }

                                                if (! $record?->email) {
                                                    return 'N/A';
                                                }

                                                return MemberDetail::query()
                                                    ->whereHas('profile', fn ($query) => $query->where('email', $record->email))
                                                    ->with('branch')
                                                    ->latest('id')
                                                    ->first()?->branch?->name ?? 'N/A';
                                            }),

                                        DatePicker::make('application_date')
                                            ->label('Application Date')
                                            ->default(now())
                                            ->required(),

                                        Select::make('status')
                                            ->options([
                                                'pending' => 'Pending',
                                                'approved' => 'Approved',
                                                'rejected' => 'Rejected',
                                                'needs_review' => 'Needs Review',
                                            ])
                                            ->default('pending')
                                            ->required(),
                                    ]),

                                Textarea::make('remarks')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('documents')
                            ->icon('heroicon-o-document-duplicate')
                            ->label('Documents')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        FileUpload::make('id_document_front')
                                            ->label('ID Document (Front)')
                                            ->disk('public')
                                            ->directory('membership-applications/id-documents')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                null,
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->visibility('public')
                                            ->columnSpan(1),

                                        FileUpload::make('id_document_back')
                                            ->label('ID Document (Back)')
                                            ->disk('public')
                                            ->directory('membership-applications/id-documents')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                null,
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable()
                                            ->openable()
                                            ->previewable()
                                            ->visibility('public')
                                            ->columnSpan(1),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString('tab'),
            ]);
    }
}
