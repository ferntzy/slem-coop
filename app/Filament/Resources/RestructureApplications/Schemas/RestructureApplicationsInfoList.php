<?php

namespace App\Filament\Resources\RestructureApplications\Schemas;

use App\Models\LoanAccount;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class RestructureApplicationsInfolist
{
    protected static function sectionCard(): array
    {
        return [
            'class' => 'rounded-2xl border border-emerald-100 dark:border-emerald-900/40 bg-white dark:bg-gray-900 shadow-sm',
        ];
    }

    public static function schema(): array
    {
        return [
            Tabs::make()
                ->tabs([
                    Tab::make('Applicant & Loan Details')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Section::make('Member Information')
                                ->description('Member identity and contact details linked to the original loan.')
                                ->icon('heroicon-o-identification')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('loanApplication.member.profile.full_name')
                                        ->label('Member Name'),
                                    TextEntry::make('loanApplication.member.profile.mobile_number')
                                        ->label('Contact Number')
                                        ->placeholder('—'),
                                    TextEntry::make('loanApplication.member.profile.email')
                                        ->label('Email')
                                        ->placeholder('—'),
                                ])
                                ->columns(3),

                            Section::make('Original Loan Details')
                                ->description('Original loan information before restructuring.')
                                ->icon('heroicon-o-document-text')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('loanApplication.type.name')
                                        ->label('Loan Type'),
                                    TextEntry::make('loanApplication.amount_requested')
                                        ->label('Original Amount')
                                        ->money('PHP'),
                                    TextEntry::make('loanApplication.term_months')
                                        ->label('Original Term (Months)'),
                                    TextEntry::make('loanApplication.status')
                                        ->label('Loan Status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'Pending' => 'warning',
                                            'Under Review' => 'info',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            'Cancelled' => 'gray',
                                            default => 'gray',
                                        }),
                                ])
                                ->columns(3),

                            Section::make('Restructure Details')
                                ->description('Updated principal, interest, and term requested for restructuring.')
                                ->icon('heroicon-o-arrow-path')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('new_principal')
                                        ->label('New Amount')
                                        ->money('PHP'),
                                    TextEntry::make('new_interest')
                                        ->label('Interest Rate')
                                        ->suffix('%'),
                                    TextEntry::make('term_months')
                                        ->label('New Term (Months)'),
                                    TextEntry::make('remarks')
                                        ->label('Remarks')
                                        ->placeholder('—')
                                        ->columnSpanFull(),
                                    TextEntry::make('status')
                                        ->label('Restructure Status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'Pending' => 'warning',
                                            'Under Review' => 'info',
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            'Cancelled' => 'gray',
                                            default => 'gray',
                                        }),
                                    TextEntry::make('created_at')
                                        ->label('Date Applied')
                                        ->dateTime('F j, Y g:i A'),
                                ])
                                ->columns(3),
                        ]),

                    Tab::make('Loan Account')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Section::make('Loan Account')
                                ->description('Current active loan account used as the base for restructuring.')
                                ->icon('heroicon-o-wallet')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('release_date')
                                        ->label('Release Date')
                                        ->getStateUsing(function ($record) {
                                            $account = LoanAccount::where('loan_application_id', $record->loan_application_id)
                                                ->where('status', 'Active')
                                                ->latest()
                                                ->first();

                                            return $account?->release_date
                                                ? Carbon::parse($account->release_date)->format('F j, Y')
                                                : '—';
                                        }),

                                    TextEntry::make('maturity_date')
                                        ->label('Maturity Date')
                                        ->getStateUsing(function ($record) {
                                            $account = LoanAccount::where('loan_application_id', $record->loan_application_id)
                                                ->where('status', 'Active')
                                                ->latest()
                                                ->first();

                                            return $account?->maturity_date
                                                ? Carbon::parse($account->maturity_date)->format('F j, Y')
                                                : '—';
                                        }),

                                    TextEntry::make('balance')
                                        ->label('Outstanding Balance')
                                        ->getStateUsing(function ($record) {
                                            $account = LoanAccount::where('loan_application_id', $record->loan_application_id)
                                                ->where('status', 'Active')
                                                ->latest()
                                                ->first();

                                            return $account?->balance;
                                        })
                                        ->money('PHP')
                                        ->placeholder('—'),

                                    TextEntry::make('interest_rate')
                                        ->label('Current Interest Rate')
                                        ->getStateUsing(function ($record) {
                                            $account = LoanAccount::where('loan_application_id', $record->loan_application_id)
                                                ->where('status', 'Active')
                                                ->latest()
                                                ->first();

                                            return $account?->interest_rate;
                                        })
                                        ->suffix('%')
                                        ->placeholder('—'),

                                    TextEntry::make('loan_account_status')
                                        ->label('Account Status')
                                        ->getStateUsing(function ($record) {
                                            $account = LoanAccount::where('loan_application_id', $record->loan_application_id)
                                                ->where('status', 'Active')
                                                ->latest()
                                                ->first();

                                            return $account?->status ?? 'No account yet';
                                        })
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'Active' => 'success',
                                            'Closed' => 'gray',
                                            'Defaulted' => 'danger',
                                            default => 'gray',
                                        }),
                                ])
                                ->columns(3),

                            Section::make('Amortization Schedule')
                                ->description('Projected payment schedule under the restructure terms.')
                                ->icon('heroicon-o-table-cells')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    ViewEntry::make('amortization_schedule')
                                        ->view('filament.infolists.restructure-amortization-schedule')
                                        ->columnSpanFull(),
                                ])
                                ->collapsed(),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
