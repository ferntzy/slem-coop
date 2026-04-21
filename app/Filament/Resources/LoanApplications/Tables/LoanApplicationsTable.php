<?php

namespace App\Filament\Resources\LoanApplications\Tables;

use App\Filament\Resources\LoanApplications\Schemas\LoanApplicationsInfolist;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationCollstat;
use App\Models\LoanApplicationStatusLog;
use App\Models\MemberDetail;
use App\Models\Notification as ModelsNotification;
use App\Models\PenaltyRule;
use App\Models\ShareCapitalTransaction;
use App\Services\CoopFeeCalculatorService;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class LoanApplicationsTable
{
    private static function createUserNotification($record, $title, $description, $notifiableType = null, $notifiableId = null): void
    {
        $userId = $record->member?->profile?->user?->user_id;

        if ($userId) {
            ModelsNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'description' => $description,
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId,
            ]);
        }
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user?->isMember()) {
                    $memberId = MemberDetail::where('profile_id', $user->profile_id)->value('id');

                    if (! $memberId) {
                        return $query->whereRaw('1 = 0');
                    }

                    $query->where('member_id', $memberId);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('member.profile.full_name')
                    ->label('Member Name')
                    ->action(
                        Action::make('view')
                            ->modalHeading(fn ($record) => 'Loan Application — '.($record->member?->profile?->full_name ?? 'N/A'))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalWidth('5xl')
                            ->infolist(fn ($record) => LoanApplicationsInfolist::schema())
                            ->extraModalFooterActions(fn ($record) => [
                                Action::make('download_pdf')
                                    ->label('Download PDF')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->color('success')
                                    ->url(fn () => route('loan-applications.pdf', [
                                        'loanApplication' => $record->loan_application_id,
                                    ]))
                                    ->openUrlInNewTab(),
                            ])
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('member.profile', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('member_details', 'loan_applications.member_id', '=', 'member_details.id')
                            ->leftJoin('profiles', 'member_details.profile_id', '=', 'profiles.profile_id')
                            ->orderBy('profiles.first_name', $direction)
                            ->orderBy('profiles.last_name', $direction)
                            ->select('loan_applications.*');
                    }),

                TextColumn::make('type.name')
                    ->label('Loan Type')
                    ->sortable()
                    ->searchable()
                    ->action(
                        Action::make('view')
                            ->modalHeading(fn ($record) => 'Loan Application — '.($record->member?->profile?->full_name ?? 'N/A'))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalWidth('5xl')
                            ->infolist(fn ($record) => LoanApplicationsInfolist::schema())
                            ->extraModalFooterActions(fn ($record) => [
                                Action::make('download_pdf')
                                    ->label('Download PDF')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->color('success')
                                    ->url(fn () => route('loan-applications.pdf', [
                                        'loanApplication' => $record->loan_application_id,
                                    ]))
                                    ->openUrlInNewTab(),
                            ])
                    ),

                TextColumn::make('amount_requested')
                    ->money('PHP')
                    ->action(
                        Action::make('view')
                            ->modalHeading(fn ($record) => 'Loan Application — '.($record->member?->profile?->full_name ?? 'N/A'))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalWidth('5xl')
                            ->infolist(fn ($record) => LoanApplicationsInfolist::schema())
                            ->extraModalFooterActions(fn ($record) => [
                                Action::make('download_pdf')
                                    ->label('Download PDF')
                                    ->icon('heroicon-o-document-arrow-down')
                                    ->color('success')
                                    ->url(fn () => route('loan-applications.pdf', [
                                        'loanApplication' => $record->loan_application_id,
                                    ]))
                                    ->openUrlInNewTab(),
                            ])
                    ),

                BadgeColumn::make('status')
                    ->label('Loan Status')
                    ->colors([
                        'warning' => 'Pending',
                        'info' => 'Under Review',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                        'gray' => 'Cancelled',
                    ]),

                TextColumn::make('term_months')
                    ->label('Term'),

                TextColumn::make('loanAccount.release_date')
                    ->label('Release Date')
                    ->placeholder('-- --, --')
                    ->formatStateUsing(function ($record) {
                        if ($record->loanAccount && $record->loanAccount->release_date) {
                            return $record->loanAccount->release_date->format('F j, Y');
                        }
                    })
                    ->extraAttributes(['style' => 'text-align: center;']),

                TextColumn::make('edit_button')
                    ->label('')
                    ->default('Edit')
                    ->color('warning')
                    ->weight('bold')
                    ->url(fn ($record) => url('/coop/loan-applications/'.$record->loan_application_id.'/edit')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([

                // ── ACTION GROUP ─────────────────────────────────────────────────
                ActionGroup::make([
                    Action::make('set_release_date')
                        ->label('Release Now')
                        ->icon('heroicon-o-calendar')
                        ->requiresConfirmation()
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return $record->status === 'Approved' && ! $record->loanAccount
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->action(function ($record) {

                            $releaseDate = now()->format('Y-m-d');
                            $term = (int) $record->term_months;
                            $principal = (float) $record->amount_requested;
                            $interestRate = (float) ($record->type?->max_interest_rate ?? 0);

                            $monthlyPrincipal = $term > 0 ? ($principal / $term) : $principal;
                            $firstMonthInterest = $principal * ($interestRate / 100) / 12;
                            $monthlyAmort = $monthlyPrincipal + $firstMonthInterest;

                            $profileId = $record->member?->profile_id ?? null;

                            $fees = app(CoopFeeCalculatorService::class)
                                ->calculate('loan_application', $principal);

                            $remainingBalance = 0;
                            $parentLoanId = null;

                            // CHECK IF RELOAN
                            if ($record->reloan_from_loan_account_id) {
                                $parentLoan = LoanAccount::find($record->reloan_from_loan_account_id);

                                if ($parentLoan) {
                                    $remainingBalance = $parentLoan->balance;
                                    $parentLoanId = $parentLoan->loan_account_id;

                                    $principal -= $remainingBalance;
                                    $principal = max(0, $principal);
                                }
                            }

                            $netRelease = ($fees['net_release_amount'] ?? 0) - $remainingBalance;
                            $netRelease = max(0, $netRelease);

                            $maturityDate = date('Y-m-d', strtotime("$releaseDate +$term months"));

                            $record->update([
                                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                                'processing_fee' => $fees['processing_fee'] ?? 0,
                                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                                'net_release_amount' => $netRelease,
                            ]);

                            LoanAccount::create([
                                'loan_application_id' => $record->loan_application_id,
                                'profile_id' => $profileId,
                                'principal_amount' => $principal,

                                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                                'processing_fee' => $fees['processing_fee'] ?? 0,
                                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                                'net_release_amount' => $netRelease,

                                'interest_rate' => $interestRate,
                                'term_months' => $term,
                                'release_date' => $releaseDate,
                                'maturity_date' => $maturityDate,
                                'monthly_amortization' => $monthlyAmort,
                                'balance' => $principal,
                                'status' => 'Active',
                                'parent_loan_account_id' => $parentLoanId,
                            ]);

                            $shareCapitalFee = (float) ($fees['shared_capital_fee'] ?? 0);

                            if ($profileId && $shareCapitalFee > 0) {
                                ShareCapitalTransaction::create([
                                    'profile_id' => $profileId,
                                    'amount' => $shareCapitalFee,
                                    'direction' => 'credit',
                                    'type' => 'deposit',
                                    'transaction_date' => $releaseDate,
                                    'reference_no' => 'LA-'.$record->loan_application_id,
                                    'notes' => 'Share capital fee from loan release.',
                                    'posted_by_user_id' => auth()->id(),
                                ]);
                            }

                            Notification::make()
                                ->title('Loan Released Successfully')
                                ->success()
                                ->send();
                        }),

                    Action::make('reloan')
                        ->label('Reloan')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return $record->loanAccount && $record->loanAccount->status === 'Active'
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->disabled(function ($record) {
                            $loan = $record->loanAccount;

                            if (! $loan) {
                                return true;
                            }

                            $paid = $loan->principal_amount - $loan->balance;
                            $required = $loan->principal_amount * 0.5;

                            return $paid < $required;
                        })
                        ->tooltip(function ($record) {
                            $loan = $record->loanAccount;

                            if (! $loan) {
                                return 'No loan account found';
                            }

                            $paid = $loan->principal_amount - $loan->balance;
                            $required = $loan->principal_amount * 0.5;

                            if ($paid < $required) {
                                return 'Not eligible: must pay at least 50% of the loan';
                            }

                            return 'Apply for reloan';
                        })
                        ->form([
                            TextInput::make('amount_requested')
                                ->label('Loan Amount')
                                ->numeric()
                                ->required(),

                            TextInput::make('term_months')
                                ->label('Term (Months)')
                                ->numeric()
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $loan = $record->loanAccount;

                            $paid = $loan->principal_amount - $loan->balance;
                            $required = $loan->principal_amount * 0.5;

                            if ($paid < $required) {
                                Notification::make()
                                    ->title('Not eligible')
                                    ->body('You must pay at least 50% before reloan.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            LoanApplication::create([
                                'member_id' => $record->member_id,
                                'loan_type_id' => $record->loan_type_id,
                                'amount_requested' => $data['amount_requested'],
                                'term_months' => $data['term_months'],
                                'status' => 'Pending',
                                'reloan_from_loan_account_id' => $loan->loan_account_id,
                                'previous_balance' => $loan->balance,
                            ]);

                            Notification::make()
                                ->title('Reloan Application Created')
                                ->success()
                                ->send();
                        }),

                    Action::make('approve_collateral')
                        ->label('Approve Collateral')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return (float) $record->amount_requested > 15000 && $record->collateral_status === 'Pending Verification'
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $profileId = $record->member?->profile_id ?? null;
                            $from = $record->collateral_status;

                            $record->update(['collateral_status' => 'Approved']);

                            LoanApplicationCollstat::create([
                                'loan_application_id' => $record->loan_application_id,
                                'from_status' => $from,
                                'to_status' => 'Approved',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Collateral Approved')
                                ->success()
                                ->send();

                            if ($profileId) {
                                app(NotificationService::class)->notifyProfile(
                                    $profileId,
                                    'Collateral Approved',
                                    "Collateral for loan application #{$record->loan_application_id} has been approved."
                                );
                            }

                            app(NotificationService::class)->notifyAdmins(
                                'Collateral approved',
                                "Collateral for loan application #{$record->loan_application_id} is approved."
                            );
                        }),

                    Action::make('request_correction')
                        ->label('Request Correction')
                        ->icon('heroicon-o-exclamation-circle')
                        ->color('danger')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return (float) $record->amount_requested > 15000 && $record->collateral_status === 'Pending Verification'
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $profileId = $record->member?->profile_id ?? null;
                            $from = $record->collateral_status;

                            $record->update(['collateral_status' => 'Rejected']);

                            LoanApplicationCollstat::create([
                                'loan_application_id' => $record->loan_application_id,
                                'from_status' => $from,
                                'to_status' => 'Rejected',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Collateral marked for correction')
                                ->warning()
                                ->send();

                            if ($profileId) {
                                app(NotificationService::class)->notifyProfile(
                                    $profileId,
                                    'Collateral requires correction',
                                    "Collateral for loan application #{$record->loan_application_id} has been marked for correction."
                                );
                            }

                            app(NotificationService::class)->notifyAdmins(
                                'Collateral correction requested',
                                "Collateral for loan application #{$record->loan_application_id} is requested for correction."
                            );
                        }),

                    Action::make('setPenaltyRule')
                        ->label('Set Penalty Rule')
                        ->icon('heroicon-o-shield-exclamation')
                        ->color('success')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return in_array($record->status, ['Pending', 'Under Review', 'Approved'], true)
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->form([
                            Select::make('penalty_rule_id')
                                ->label('Penalty Rule')
                                ->options(fn () => PenaltyRule::where('status', 'active')->pluck('name', 'id'))
                                ->searchable()
                                ->preload()
                                ->required()
                                ->default(fn ($record) => $record->penalty_rule_id ?: PenaltyRule::where('is_default', true)->value('id'))
                                ->helperText('Choose the penalty rule to apply if this loan becomes overdue.'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'penalty_rule_id' => $data['penalty_rule_id'],
                            ]);

                            Notification::make()
                                ->title('Penalty Rule Updated')
                                ->body('The penalty rule for this loan application has been updated.')
                                ->success()
                                ->send();
                        }),

                    Action::make('underReview')
                        ->label('Mark Under Review')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return $record->status === 'Pending'
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->action(function ($record) {
                            $profileId = $record->member?->profile_id ?? null;
                            $from = $record->status;
                            $record->update(['status' => 'Under Review']);

                            LoanApplicationStatusLog::create([
                                'loan_application_id' => $record->loan_application_id,
                                'from_status' => $from,
                                'to_status' => 'Under Review',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Marked Under Review')
                                ->success()
                                ->send();

                            if ($profileId) {
                                app(NotificationService::class)->notifyProfile(
                                    $profileId,
                                    'Loan under review',
                                    "Your loan application #{$record->loan_application_id} is now under review."
                                );
                            }

                            app(NotificationService::class)->notifyAdmins(
                                'Loan under review',
                                "Loan application #{$record->loan_application_id} moved to Under Review."
                            );
                        }),

                    // ── DOWNLOAD LOAN FORM (with preview before download) ────────
                    Action::make('downloadLoanPdf')
                        ->label('Download Loan Form')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->modalHeading(fn ($record) => 'Loan Form — '.($record->member?->profile?->full_name ?? 'N/A'))
                        ->modalContent(fn ($record) => new HtmlString('
                            <iframe
                                src="'.route('loan-applications.pdf', ['loanApplication' => $record->loan_application_id]).'"
                                style="width:100%; height:75vh; border:none; border-radius:6px;"
                            ></iframe>
                        '))
                        ->modalWidth('7xl')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->extraModalFooterActions(fn ($record) => [
                            Action::make('confirmDownload')
                                ->label('Download PDF')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->color('success')
                                ->url(fn () => route('loan-applications.pdf', [
                                    'loanApplication' => $record->loan_application_id,
                                ]))
                                ->openUrlInNewTab(),
                        ]),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return in_array($record->status, ['Pending', 'Under Review'], true)
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->action(function ($record) {
                            $profileId = $record->member?->profile_id ?? null;

                            if (! ((auth()->user()?->isHeadOffice() ?? false) || auth()->user()?->isBranchScoped())) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if ($record->approved_at) {
                                Notification::make()
                                    ->title('Already approved')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            $from = $record->status;
                            $record->update([
                                'status' => 'Approved',
                                'approved_at' => now(),
                            ]);

                            LoanApplicationStatusLog::create([
                                'loan_application_id' => $record->loan_application_id,
                                'from_status' => $from,
                                'to_status' => 'Approved',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            self::createUserNotification(
                                $record,
                                'Loan Application',
                                'Your loan application has been approved! waiting for release date.',
                                'loan_application',
                                $record->loan_application_id
                            );

                            Notification::make()
                                ->title('Loan Application Approved')
                                ->body('Please set the release date to create the loan account.')
                                ->success()
                                ->send();

                            if ($profileId) {
                                app(NotificationService::class)->notifyProfile(
                                    $profileId,
                                    'Loan application approved',
                                    "Your loan application #{$record->loan_application_id} has been approved.",
                                    notifiableType: 'loan_application',
                                    notifiableId: $record->loan_application_id
                                );
                            }

                            app(NotificationService::class)->notifyAdmins(
                                'Loan application approved',
                                "Loan application #{$record->loan_application_id} has been approved.",
                                notifiableType: 'loan_application',
                                notifiableId: $record->loan_application_id
                            );
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return in_array($record->status, ['Pending', 'Under Review'], true)
                                && (($user?->isHeadOffice() ?? false) || $user?->isBranchScoped());
                        })
                        ->form([Textarea::make('reason')->required()])
                        ->action(function ($record, array $data) {
                            $profileId = $record->member?->profile_id ?? null;

                            if (! ((auth()->user()?->isHeadOffice() ?? false) || auth()->user()?->isBranchScoped())) {
                                Notification::make()
                                    ->title('Unauthorized')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $from = $record->status;
                            $record->update(['status' => 'Rejected']);

                            LoanApplicationStatusLog::create([
                                'loan_application_id' => $record->loan_application_id,
                                'from_status' => $from,
                                'to_status' => 'Rejected',
                                'changed_by_user_id' => auth()->id(),
                                'reason' => $data['reason'],
                                'changed_at' => now(),
                            ]);

                            self::createUserNotification(
                                $record,
                                'Loan application was rejected',
                                $data['reason'],
                                'loan_application',
                                $record->loan_application_id
                            );

                            Notification::make()
                                ->title('Rejected')
                                ->success()
                                ->send();

                            if ($profileId) {
                                app(NotificationService::class)->notifyProfile(
                                    $profileId,
                                    'Loan application rejected',
                                    "Your loan application #{$record->loan_application_id} has been rejected. Reason: {$data['reason']}",
                                    notifiableType: 'loan_application',
                                    notifiableId: $record->loan_application_id
                                );
                            }

                            app(NotificationService::class)->notifyAdmins(
                                'Loan application rejected',
                                "Loan application #{$record->loan_application_id} has been rejected. Reason: {$data['reason']}",
                                notifiableType: 'loan_application',
                                notifiableId: $record->loan_application_id
                            );
                        }),

                    // ── DELETE ───────────────────────────────────────────────────
                    Action::make('delete')
                        ->label('Delete')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Delete Loan Application')
                        ->modalDescription('This action cannot be undone.')
                        ->visible(function ($record): bool {
                            $user = auth()->user();

                            return in_array($record->status, ['Pending', 'Approved', 'Cancelled', 'Rejected'], true)
                                && ($user?->isHeadOffice() || $user?->isBranchScoped());
                        })
                        ->action(function ($record) {
                            $record->delete();

                            Notification::make()
                                ->title('Loan Application Deleted')
                                ->success()
                                ->send();
                        }),
                ])
                    ->visible(fn ($record): bool => ! $record->trashed())
                    ->tooltip('Actions'),

                ActionGroup::make([
                    Action::make('restore')
                        ->label('Restore')
                        ->icon('heroicon-o-arrow-path')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->restore();

                            Notification::make()
                                ->title('Loan Application Restored')
                                ->success()
                                ->send();
                        }),
                ])
                    ->visible(function ($record): bool {
                        $user = auth()->user();

                        return $record->trashed() && ($user?->isHeadOffice() || $user?->isBranchScoped());
                    })
                    ->tooltip('Trashed Actions'),
            ])
            ->bulkActions([])
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->defaultSort('created_at', 'desc');
    }
}
