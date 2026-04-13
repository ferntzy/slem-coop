<?php

namespace App\Filament\Resources\LoanApplications\Schemas;

use App\Services\LoanAmortizationService;
use App\Services\LoanScheduleService;
use Carbon\Carbon;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Storage;

class LoanApplicationsInfolist
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
                                ->description('Basic member identity and contact details.')
                                ->icon('heroicon-o-identification')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('member.profile.full_name')
                                        ->label('Member Name'),

                                    TextEntry::make('member.profile.mobile_number')
                                        ->label('Contact Number')
                                        ->placeholder('—'),

                                    TextEntry::make('member.profile.email')
                                        ->label('Email')
                                        ->placeholder('—'),
                                ])
                                ->columns(3),

                            Section::make('Loan Details')
                                ->description('Requested loan type, amount, term, and purpose.')
                                ->icon('heroicon-o-document-text')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('type.name')
                                        ->label('Loan Type'),

                                    TextEntry::make('amount_requested')
                                        ->label('Amount Requested')
                                        ->money('PHP'),

                                    TextEntry::make('term_months')
                                        ->label('Term (Months)'),

                                    TextEntry::make('purpose')
                                        ->label('Purpose')
                                        ->placeholder('—')
                                        ->columnSpanFull(),
                                ])
                                ->columns(3),

                            Section::make('Coop Fee Breakdown')
                                ->description('Deducted cooperative charges and computed net release.')
                                ->icon('heroicon-o-building-library')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('shared_capital_fee')
                                        ->label('Shared Capital Fee')
                                        ->money('PHP')
                                        ->placeholder('—'),

                                    TextEntry::make('insurance_fee')
                                        ->label('Insurance Fee')
                                        ->money('PHP')
                                        ->placeholder('—'),

                                    TextEntry::make('processing_fee')
                                        ->label('Processing Fee')
                                        ->money('PHP')
                                        ->placeholder('—'),

                                    TextEntry::make('coop_fee_total')
                                        ->label('Total Coop Fee')
                                        ->money('PHP')
                                        ->weight('bold')
                                        ->placeholder('—'),

                                    TextEntry::make('net_release_amount')
                                        ->label('Net Release Amount')
                                        ->money('PHP')
                                        ->weight('bold')
                                        ->placeholder('—'),
                                ])
                                ->columns(2),

                            Section::make('Collateral')
                                ->description('Collateral requirement and uploaded supporting file.')
                                ->icon('heroicon-o-shield-check')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('collateral_status')
                                        ->label('Collateral Status')
                                        ->badge()
                                        ->formatStateUsing(function ($state, $record) {
                                            $threshold = (float) ($record->type?->collateral_threshold ?? 0);
                                            $requiresCollateral = (bool) ($record->type?->requires_collateral ?? false);

                                            if (! $requiresCollateral || (float) $record->amount_requested <= $threshold) {
                                                return 'Not Required';
                                            }

                                            return $state ?? '—';
                                        })
                                        ->color(function ($state, $record) {
                                            $threshold = (float) ($record->type?->collateral_threshold ?? 0);
                                            $requiresCollateral = (bool) ($record->type?->requires_collateral ?? false);

                                            if (! $requiresCollateral || (float) $record->amount_requested <= $threshold) {
                                                return 'gray';
                                            }

                                            return match ($state) {
                                                'Approved' => 'success',
                                                'Rejected' => 'danger',
                                                'Pending Verification' => 'warning',
                                                default => 'gray',
                                            };
                                        }),

                                    TextEntry::make('collateral_document')
                                        ->label('Collateral File')
                                        ->badge()
                                        ->formatStateUsing(fn ($state) => filled($state) ? 'View Document' : 'No File')
                                        ->url(fn ($record) => filled($record->collateral_document)
                                            ? Storage::disk('public')->url($record->collateral_document)
                                            : null)
                                        ->openUrlInNewTab()
                                        ->color(fn ($state) => filled($state) ? 'primary' : 'gray'),
                                ])
                                ->columns(2),
                        ]),

                    Tab::make('Status & Cash Flow')
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Section::make('Application Status')
                                ->description('Current application state and key dates.')
                                ->icon('heroicon-o-information-circle')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('status')
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

                                    TextEntry::make('approved_at')
                                        ->label('Approved At')
                                        ->dateTime('F j, Y g:i A')
                                        ->placeholder('Not yet approved'),

                                    TextEntry::make('created_at')
                                        ->label('Date Applied')
                                        ->dateTime('F j, Y g:i A'),
                                ])
                                ->columns(3),

                            Section::make('Cash Flow Analysis')
                                ->description('Income, expenses, and allowable payment summary.')
                                ->icon('heroicon-o-calculator')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('total_income')
                                        ->label('Total Income')
                                        ->getStateUsing(fn ($record) => '₱'.number_format(
                                            $record->cashflows()->where('row_type', 'income')->sum('amount'),
                                            2
                                        )),

                                    TextEntry::make('total_expenses')
                                        ->label('Total Expenses')
                                        ->getStateUsing(fn ($record) => '₱'.number_format(
                                            $record->cashflows()->whereIn('row_type', ['expense', 'debt'])->sum('amount'),
                                            2
                                        )),

                                    TextEntry::make('net_cash_flow')
                                        ->label('Net Cash Flow')
                                        ->getStateUsing(function ($record) {
                                            $income = $record->cashflows()->where('row_type', 'income')->sum('amount');
                                            $expenses = $record->cashflows()->whereIn('row_type', ['expense', 'debt'])->sum('amount');

                                            return '₱'.number_format($income - $expenses, 2);
                                        }),

                                    TextEntry::make('allowed_payment')
                                        ->label('Allowed Payment (40%)')
                                        ->getStateUsing(function ($record) {
                                            $income = $record->cashflows()->where('row_type', 'income')->sum('amount');
                                            $expenses = $record->cashflows()->whereIn('row_type', ['expense', 'debt'])->sum('amount');

                                            return '₱'.number_format(($income - $expenses) * 0.40, 2);
                                        }),
                                ])
                                ->columns(4),

                            Section::make('Loan Capacity Evaluation')
                                ->description('Repayment capacity assessment based on current cash flow.')
                                ->icon('heroicon-o-scale')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('proposed_loan_payment')
                                        ->label('Proposed Monthly Payment')
                                        ->getStateUsing(function ($record) {
                                            $principal = (float) $record->amount_requested;
                                            $term = max((int) $record->term_months, 1);
                                            $interestRate = (float) ($record->type?->max_interest_rate ?? 0);
                                            $payment = ($principal / $term) + ($principal * ($interestRate / 100) / 12);

                                            return '₱'.number_format($payment, 2);
                                        }),

                                    TextEntry::make('capacity_status')
                                        ->label('Capacity Status')
                                        ->badge()
                                        ->getStateUsing(function ($record) {
                                            $income = $record->cashflows()->where('row_type', 'income')->sum('amount');
                                            $expenses = $record->cashflows()->whereIn('row_type', ['expense', 'debt'])->sum('amount');
                                            $allowed = ($income - $expenses) * 0.40;

                                            $principal = (float) $record->amount_requested;
                                            $term = max((int) $record->term_months, 1);
                                            $interestRate = (float) ($record->type?->max_interest_rate ?? 0);
                                            $payment = ($principal / $term) + ($principal * ($interestRate / 100) / 12);

                                            return match (true) {
                                                $allowed >= $payment => 'Safe',
                                                $allowed >= ($payment * 0.8) => 'Slightly Risky',
                                                default => 'High Risk',
                                            };
                                        })
                                        ->color(fn ($state) => match ($state) {
                                            'Safe' => 'success',
                                            'Slightly Risky' => 'warning',
                                            'High Risk' => 'danger',
                                            default => 'gray',
                                        }),

                                    TextEntry::make('evaluation_notes')
                                        ->label('Evaluation Notes')
                                        ->placeholder('—')
                                        ->columnSpanFull(),

                                    TextEntry::make('bici_notes')
                                        ->label('BI/CI Notes')
                                        ->placeholder('—')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),

                            Section::make('Supporting Documents')
                                ->description('Uploaded cash flow evidence for review.')
                                ->icon('heroicon-o-paper-clip')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    ViewEntry::make('cashflow_documents')
                                        ->view('filament.infolists.cashflow-documents')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn ($record) => filled($record->cashflow_documents))
                                ->columnSpanFull(),
                        ]),

                    Tab::make('Loan Account')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Section::make('Loan Account')
                                ->description('Release details, balance, rate, and current loan account state.')
                                ->icon('heroicon-o-wallet')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('loanAccount.release_date')
                                        ->label('Release Date')
                                        ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('F j, Y') : null)
                                        ->placeholder('Not yet released'),

                                    TextEntry::make('loanAccount.maturity_date')
                                        ->label('Maturity Date')
                                        ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('F j, Y') : null)
                                        ->placeholder('—'),

                                    TextEntry::make('loanAccount.balance')
                                        ->label('Outstanding Balance')
                                        ->money('PHP')
                                        ->placeholder('—'),

                                    TextEntry::make('loanAccount.interest_rate')
                                        ->label('Interest Rate / Month')
                                        ->formatStateUsing(fn ($state) => $state !== null ? $state.'%' : null)
                                        ->placeholder('—'),

                                    TextEntry::make('loanAccount.status')
                                        ->label('Account Status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'Active' => 'success',
                                            'Closed' => 'gray',
                                            'Defaulted' => 'danger',
                                            default => 'gray',
                                        })
                                        ->placeholder('No account yet'),
                                ])
                                ->columns(3),

                            Section::make('Penalty Breakdown')
                                ->description('Computed penalties based on the loan schedule.')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    ViewEntry::make('loan_penalty_breakdown')
                                        ->view('filament.infolists.loan-penalty-breakdown')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn ($record) => filled($record->loanAccount))
                                ->columnSpanFull(),

                            Section::make('Amortization Schedule')
                                ->description('Month-by-month loan payment breakdown.')
                                ->icon('heroicon-o-table-cells')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    ViewEntry::make('amortization_schedule')
                                        ->view('filament.infolists.loan-amortization-schedule')
                                        ->viewData([
                                            'getSchedule' => function ($record) {
                                                if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                                    return null;
                                                }

                                                return app(LoanAmortizationService::class)->generate(
                                                    loanAmount: (float) $record->amount_requested,
                                                    monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                                    termMonths: (int) $record->term_months,
                                                    releaseDate: $record->loanAccount->release_date,
                                                );
                                            },
                                        ])
                                        ->columnSpanFull(),
                                ])
                                ->collapsed(),
                        ]),

                    Tab::make('Loan History')
                        ->label('Balance and Payment History')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Section::make('Audit')
                                ->description('Remaining balance, payment progress, and next due summary.')
                                ->icon('heroicon-o-clipboard-document-check')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    TextEntry::make('remaining_principal')
                                        ->label('Remaining Principal')
                                        ->money('PHP')
                                        ->state(function ($record) {
                                            return (float) ($record->loanAccount->balance ?? $record->loanAccount->principal_amount ?? 0);
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('total_interest')
                                        ->label('Remaining Interest')
                                        ->money('PHP')
                                        ->state(function ($record) {
                                            if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                                return 0;
                                            }

                                            $schedule = collect(app(LoanAmortizationService::class)->generate(
                                                loanAmount: (float) $record->amount_requested,
                                                monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                                termMonths: (int) $record->term_months,
                                                releaseDate: $record->loanAccount->release_date,
                                            ));

                                            return (float) $schedule->sum(fn ($row) => (float) ($row['interest'] ?? 0));
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('total_penalty')
                                        ->label('Penalty')
                                        ->money('PHP')
                                        ->state(function ($record) {
                                            if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                                return 0;
                                            }

                                            $schedule = collect(app(LoanAmortizationService::class)->generate(
                                                loanAmount: (float) $record->amount_requested,
                                                monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                                termMonths: (int) $record->term_months,
                                                releaseDate: $record->loanAccount->release_date,
                                            ));

                                            return (float) $schedule->sum(fn ($row) => (float) ($row['penalty'] ?? 0));
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('total_payable_amount')
                                        ->label('Remaining Balance')
                                        ->money('PHP')
                                        ->state(function ($record) {
                                            if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                                return (float) ($record->loanAccount->balance ?? 0);
                                            }

                                            $remainingPrincipal = (float) ($record->loanAccount->balance ?? $record->loanAccount->principal_amount ?? 0);

                                            $schedule = collect(app(LoanAmortizationService::class)->generate(
                                                loanAmount: (float) $record->amount_requested,
                                                monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                                termMonths: (int) $record->term_months,
                                                releaseDate: $record->loanAccount->release_date,
                                            ));

                                            $totalInterest = (float) $schedule->sum(fn ($row) => (float) ($row['interest'] ?? 0));
                                            $totalPenalty = (float) $schedule->sum(fn ($row) => (float) ($row['penalty'] ?? 0));

                                            return $remainingPrincipal + $totalInterest + $totalPenalty;
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('months_left')
                                        ->label('Months Left to Pay')
                                        ->state(function ($record) {
                                            $loan = $record->loanAccount;

                                            if (! $loan) {
                                                return '—';
                                            }

                                            $term = (int) ($record->term_months ?? $loan->term_months ?? 0);
                                            $monthly = (float) ($loan->monthly_amortization ?? 0);
                                            $paid = (float) $loan->loanPayments()->sum('amount_paid');

                                            if ($term <= 0) {
                                                return '—';
                                            }

                                            if ($monthly <= 0) {
                                                return $term.' months';
                                            }

                                            $monthsPaid = floor($paid / $monthly);
                                            $monthsLeft = max(0, $term - $monthsPaid);

                                            return $monthsLeft.' months';
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('payment_progress')
                                        ->label('Payment Progress')
                                        ->getStateUsing(function ($record): float {
                                            $loanAccount = $record->loanAccount;

                                            if (! $loanAccount) {
                                                return 0.0;
                                            }

                                            $principal = (float) ($loanAccount->principal_amount ?? 0);
                                            $balance = (float) ($loanAccount->balance ?? 0);

                                            if ($principal <= 0) {
                                                return 0.0;
                                            }

                                            $progress = (($principal - $balance) / $principal) * 100;

                                            return min(100.0, max(0.0, round($progress, 2)));
                                        })
                                        ->formatStateUsing(fn ($state): string => number_format((float) $state, 2).'%')
                                        ->badge()
                                        ->color(fn ($state) => match (true) {
                                            (float) $state >= 100.0 => 'success',
                                            (float) $state <= 0.0 => 'gray',
                                            default => 'warning',
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('next_due_date')
                                        ->label('Next Due Date')
                                        ->state(function ($record) {
                                            if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                                return '—';
                                            }

                                            $schedule = collect(app(LoanAmortizationService::class)->generate(
                                                loanAmount: (float) $record->amount_requested,
                                                monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                                termMonths: (int) $record->term_months,
                                                releaseDate: $record->loanAccount->release_date,
                                            ));

                                            $next = $schedule->first();

                                            return $next['due_date'] ?? '—';
                                        })
                                        ->placeholder('—'),

                                    TextEntry::make('loanAccount.status')
                                        ->label('Loan Account Status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'Active' => 'success',
                                            'Closed' => 'gray',
                                            'Defaulted' => 'danger',
                                            default => 'gray',
                                        })
                                        ->placeholder('—'),
                                ])
                                ->columns(3),

                            Section::make('Loan Payment History')
                                ->description('Latest and older payment records for this loan.')
                                ->icon('heroicon-o-clock')
                                ->extraAttributes(static::sectionCard())
                                ->schema([
                                    RepeatableEntry::make('latest_payment')
                                        ->label('Latest Payment')
                                        ->getStateUsing(function ($record): array {
                                            $loanAccount = $record->loanAccount;

                                            if (! $loanAccount) {
                                                return [];
                                            }

                                            $history = app(LoanScheduleService::class)->buildPaymentHistory($loanAccount);

                                            if (empty($history)) {
                                                return [];
                                            }

                                            return [collect($history)->first()];
                                        })
                                        ->schema([
                                            TextEntry::make('payment_date')
                                                ->label('Payment Date')
                                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('F j, Y') : '—'),

                                            TextEntry::make('amount_paid')
                                                ->label('Amount Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                            TextEntry::make('payment_method')
                                                ->label('Payment Method')
                                                ->placeholder('—'),

                                            TextEntry::make('principal_paid')
                                                ->label('Principal Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                            TextEntry::make('interest_paid')
                                                ->label('Interest Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                            TextEntry::make('penalty_paid')
                                                ->label('Penalty Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                            TextEntry::make('reference_number')
                                                ->label('Reference No.')
                                                ->placeholder('—'),

                                            TextEntry::make('notes')
                                                ->label('Notes')
                                                ->placeholder('—')
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(4)
                                        ->contained(),

                                    Section::make('Older Payments')
                                        ->description('Previous payment entries beyond the latest payment.')
                                        ->icon('heroicon-o-archive-box')
                                        ->extraAttributes(static::sectionCard())
                                        ->schema([
                                            RepeatableEntry::make('older_payments')
                                                ->label('')
                                                ->getStateUsing(function ($record): array {
                                                    $loanAccount = $record->loanAccount;

                                                    if (! $loanAccount) {
                                                        return [];
                                                    }

                                                    $history = app(LoanScheduleService::class)->buildPaymentHistory($loanAccount);

                                                    if (count($history) <= 1) {
                                                        return [];
                                                    }

                                                    return collect($history)->skip(1)->values()->toArray();
                                                })
                                                ->schema([
                                                    TextEntry::make('payment_date')
                                                        ->label('Payment Date')
                                                        ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('F j, Y') : '—'),

                                                    TextEntry::make('amount_paid')
                                                        ->label('Amount Paid')
                                                        ->money('PHP')
                                                        ->placeholder('—'),

                                                    TextEntry::make('payment_method')
                                                        ->label('Payment Method')
                                                        ->placeholder('—'),

                                                    TextEntry::make('principal_paid')
                                                        ->label('Principal Paid')
                                                        ->money('PHP')
                                                        ->placeholder('—'),

                                                    TextEntry::make('interest_paid')
                                                        ->label('Interest Paid')
                                                        ->money('PHP')
                                                        ->placeholder('—'),

                                                    TextEntry::make('penalty_paid')
                                                        ->label('Penalty Paid')
                                                        ->money('PHP')
                                                        ->placeholder('—'),

                                                    TextEntry::make('reference_number')
                                                        ->label('Reference No.')
                                                        ->placeholder('—'),

                                                    TextEntry::make('notes')
                                                        ->label('Notes')
                                                        ->placeholder('—')
                                                        ->columnSpanFull(),
                                                ])
                                                ->columns(4)
                                                ->contained(),
                                        ])
                                        ->collapsible()
                                        ->collapsed(),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ];
    }
}
