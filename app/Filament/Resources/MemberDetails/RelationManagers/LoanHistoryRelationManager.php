<?php

namespace App\Filament\Resources\MemberDetails\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use App\Services\LoanScheduleService;

class LoanHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'loanApplications';

    protected static ?string $title = 'Loan History';

     protected static bool $shouldSkipAuthorization = true;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('loan_application_id')
                    ->label('Loan ID')
                    ->sortable(),

                TextColumn::make('type.name')
                    ->label('Loan Type')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('amount_requested')
                    ->label('Amount Requested')
                    ->money('PHP')
                    ->sortable(),

                // TextColumn::make('term_months')
                //     ->label('Term (Months)')
                //     ->sortable(),

                // TextColumn::make('purpose')
                //     ->label('Purpose')
                //     ->limit(40)
                //     ->tooltip(fn ($record) => $record->purpose),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved'  => 'success',
                        'Pending'   => 'warning',
                        'Rejected'  => 'danger',
                        'Paid'      => 'info',
                        'Defaulted' => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->placeholder('—'),

                // TextColumn::make('approved_at')
                //     ->label('Approved')
                //     ->dateTime('M d, Y')
                //     ->sortable()
                //     ->placeholder('—'),
            ])
            ->actions([
                ViewAction::make('inspect')
                    ->label('Inspect')
                    ->modalHeading('Loan Details')
                    ->modalWidth('7xl')
                    ->infolist([
                        Section::make('Loan Information')
                            ->schema([
                                // TextEntry::make('loan_application_id')
                                //     ->label('Loan ID'),

                                TextEntry::make('type.name')
                                    ->label('Loan Type')
                                    ->placeholder('—'),

                                TextEntry::make('amount_requested')
                                    ->label('Amount Requested')
                                    ->money('PHP'),

                                TextEntry::make('term_months')
                                    ->label('Term (Months)'),

                                TextEntry::make('purpose')
                                    ->label('Purpose')
                                    ->placeholder('—'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),

                                TextEntry::make('submitted_at')
                                    ->label('Submitted')
                                    ->dateTime('M d, Y')
                                    ->placeholder('—'),

                                TextEntry::make('approved_at')
                                    ->label('Approved')
                                    ->dateTime('M d, Y')
                                    ->placeholder('—'),
                            ])
                            ->columns(2),

                        Section::make('Balance Audit')
                            ->schema([
                                TextEntry::make('remaining_principal')
                                    ->label('Remaining Principal')
                                    ->money('PHP')
                                    ->state(function ($record) {
                                        return (float) ($record->loanAccount->balance ?? $record->loanAccount->principal_amount ?? 0);
                                    })
                                    ->placeholder('—'),

                                TextEntry::make('total_interest')
                                    ->label('Total Interest')
                                    ->money('PHP')
                                    ->state(function ($record) {
                                        if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                            return 0;
                                        }

                                        $schedule = collect(app(\App\Services\LoanAmortizationService::class)->generate(
                                            loanAmount: (float) $record->amount_requested,
                                            monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                            termMonths: (int) $record->term_months,
                                            releaseDate: $record->loanAccount->release_date,
                                        ));

                                        return (float) $schedule->sum(fn ($row) => (float) ($row['interest'] ?? 0));
                                    })
                                    ->placeholder('—'),

                                TextEntry::make('total_penalty')
                                    ->label('Total Penalty')
                                    ->money('PHP')
                                    ->state(function ($record) {
                                        if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                         return 0;
                                        }

                                        $schedule = collect(app(\App\Services\LoanAmortizationService::class)->generate(
                                            loanAmount: (float) $record->amount_requested,
                                            monthlyInterestRatePercent: (float) $record->loanAccount->interest_rate,
                                            termMonths: (int) $record->term_months,
                                            releaseDate: $record->loanAccount->release_date,
                                        ));

                                        return (float) $schedule->sum(fn ($row) => (float) ($row['penalty'] ?? 0));
                                    })
                                    ->placeholder('—'),

                                TextEntry::make('total_payable_amount')
                                    ->label('Total Payable Amount')
                                    ->money('PHP')
                                    ->state(function ($record) {
                                        if (! $record->loanAccount || ! $record->loanAccount->release_date) {
                                            return (float) ($record->loanAccount->balance ?? 0);
                                        }

                                        $remainingPrincipal = (float) ($record->loanAccount->balance ?? $record->loanAccount->principal_amount ?? 0);

                                        $schedule = collect(app(\App\Services\LoanAmortizationService::class)->generate(
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
                                        ->label('Months Left')
                                        ->state(function ($record) {
                                            $loan = $record->loanAccount;

                                            if (! $loan) {
                                                return '—';
                                            }

                                            $term = (int) ($record->term_months ?? $loan->term_months ?? 0);

                                            if ($term <= 0) {
                                                return '—';
                                            }

                                            $schedule = app(LoanScheduleService::class)->build($loan);

                                            if ($schedule === []) {
                                                return $term . ' months';
                                            }

                                            $term = count($schedule);

                                            $monthsPaid = (int) collect($schedule)
                                                ->filter(fn (array $row): bool => ($row['status'] ?? null) === 'Paid')
                                                ->count();
                                            $monthsLeft = max(0, $term - $monthsPaid);

                                            return $monthsLeft . ' months';
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
                                        ->formatStateUsing(fn ($state): string => number_format((float) $state, 2) . '%')
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

                                            $schedule = collect(app(\App\Services\LoanAmortizationService::class)->generate(
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


                        Section::make('Payment History')
                            ->schema([
                                RepeatableEntry::make('latest_payment')
                                    ->label('Latest Payment')
                                    ->getStateUsing(function ($record): array {
                                        $loanAccount = $record->loanAccount;

                                        if (! $loanAccount) {
                                            return [];
                                        }

                                        $history = app(\App\Services\LoanScheduleService::class)->buildPaymentHistory($loanAccount);

                                        if (empty($history)) {
                                            return [];
                                        }

                                        return [collect($history)->first()];
                                    })
                                    ->schema([
                                        TextEntry::make('payment_date')
                                            ->label('Payment Date')
                                            ->date('M d, Y')
                                            ->placeholder('—'),

                                        TextEntry::make('amount_paid')
                                            ->label('Amount Paid')
                                            ->money('PHP')
                                            ->placeholder('—'),

                                        TextEntry::make('principal_paid')
                                            ->label('Principal Paid')
                                            ->money('PHP')
                                            ->placeholder('—'),

                                        TextEntry::make('interest_paid')
                                            ->label('Interest Paid')
                                            ->money('PHP')
                                            ->placeholder('—'),

                                    ])
                                    ->columns(2),

                                Section::make('Older Payments')
                                    ->schema([
                                        RepeatableEntry::make('older_payments')
                                        ->label('')
                                        ->getStateUsing(function ($record): array {
                                            $loanAccount = $record->loanAccount;

                                            if (! $loanAccount) {
                                                return [];
                                            }

                                            $history = app(\App\Services\LoanScheduleService::class)->buildPaymentHistory($loanAccount);

                                            if (count($history) <= 1) {
                                                return [];
                                            }

                                            return collect($history)->skip(1)->values()->toArray();
                                        })
                                        ->schema([
                                            TextEntry::make('payment_date')
                                                ->label('Payment Date')
                                                ->date('M d, Y')
                                                ->placeholder('—'),

                                            TextEntry::make('amount_paid')
                                                ->label('Amount Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                            TextEntry::make('principal_paid')
                                                ->label('Principal Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                            TextEntry::make('interest_paid')
                                                ->label('Interest Paid')
                                                ->money('PHP')
                                                ->placeholder('—'),

                                        ])
                                        ->columns(2),
                                ])
                                ->collapsible()
                                        ->collapsed(),
                             ])
                                ->collapsed(false),
                                                ]),
                                        ])
                                        ->defaultSort('loan_application_id', 'desc')
                                        ->striped()
                                        ->emptyStateHeading('No loan applications found')
                                        ->emptyStateDescription('This member has no loan application records yet.');
                                        }
                                    }
