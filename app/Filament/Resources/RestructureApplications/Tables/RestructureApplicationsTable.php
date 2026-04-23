<?php

namespace App\Filament\Resources\RestructureApplications\Tables;

use App\Filament\Resources\RestructureApplications\Schemas\RestructureApplicationsInfolist;
use App\Models\LoanAccount;
use App\Models\RestructureApplicationStatusLog;
use App\Services\CoopFeeCalculatorService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RestructureApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user?->isMember()) {
                    $query->whereHas('loanApplication.member', fn (Builder $memberQuery) => $memberQuery->where('profile_id', $user->profile_id));
                }

                return $query->with([
                    'loanApplication.member.profile',
                    'loanApplication.type',
                ]);
            })
            ->columns([

                TextColumn::make('loanApplication.member.profile.full_name')
                    ->label('Member Name')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('loanApplication.member.profile', function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('loan_applications', 'restructure_applications.loan_application_id', '=', 'loan_applications.loan_application_id')
                            ->leftJoin('member_details', 'loan_applications.member_id', '=', 'member_details.id')
                            ->leftJoin('profiles', 'member_details.profile_id', '=', 'profiles.profile_id')
                            ->orderBy('profiles.first_name', $direction)
                            ->orderBy('profiles.last_name', $direction)
                            ->select('restructure_applications.*');
                    }),

                TextColumn::make('loanApplication.type.name')
                    ->label('Loan Type')
                    ->sortable(),

                TextColumn::make('new_principal')
                    ->label('New Amount')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('new_interest')
                    ->label('Interest Rate')
                    ->suffix('%')
                    ->sortable(),

                TextColumn::make('term_months')
                    ->label('New Term (Months)')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'Pending',
                        'info' => 'Under Review',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                        'gray' => 'Cancelled',
                    ]),

                TextColumn::make('created_at')
                    ->label('Date Applied')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->label('View')
                        ->icon('heroicon-o-eye')
                        ->modalHeading(fn ($record) => 'Restructure Application #'.$record->restructure_application_id)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalWidth('5xl')
                        ->infolist(fn () => RestructureApplicationsInfolist::schema()),

                    Action::make('underReview')
                        ->label('Mark Under Review')
                        ->icon('heroicon-o-eye')
                        ->color('info')
                        ->visible(fn ($record) => $record->status === 'Pending')
                        ->action(function ($record) {
                            $from = $record->status;
                            $record->update(['status' => 'Under Review']);

                            RestructureApplicationStatusLog::create([
                                'restructure_application_id' => $record->restructure_application_id,
                                'from_status' => $from,
                                'to_status' => 'Under Review',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Marked Under Review')
                                ->success()
                                ->send();
                        }),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => in_array($record->status, ['Pending', 'Under Review'], true))
                        ->action(function ($record) {
                            $oldLoanAccount = LoanAccount::find($record->old_loan_account_id);

                            if (! $oldLoanAccount) {
                                Notification::make()
                                    ->title('Old loan account not found')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $principal = (float) $record->new_principal;
                            $interestRate = (float) $record->new_interest;
                            $term = (int) $record->term_months;
                            $release = now()->toDateString();

                            $monthlyPrincipal = $term > 0 ? ($principal / $term) : $principal;
                            $firstMonthInterest = $principal * ($interestRate / 100) / 12;
                            $monthlyAmort = $monthlyPrincipal + $firstMonthInterest;

                            $fees = app(CoopFeeCalculatorService::class)
                                ->calculate('restructure', $principal);

                            $record->update([
                                'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                                'insurance_fee' => $fees['insurance_fee'] ?? 0,
                                'processing_fee' => $fees['processing_fee'] ?? 0,
                                'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                                'net_release_amount' => $fees['net_release_amount'] ?? 0,
                                'status' => 'Approved',
                            ]);

                            DB::transaction(function () use ($record, $oldLoanAccount, $principal, $interestRate, $term, $release, $monthlyAmort, $fees) {

                                $oldLoanAccount->update([
                                    'status' => 'Restructured',
                                    'restructured_at' => now(),
                                ]);

                                $newLoan = LoanAccount::create([
                                    'loan_application_id' => $record->loan_application_id,
                                    'profile_id' => $record->loanApplication?->member?->profile_id,
                                    'principal_amount' => $principal,
                                    'shared_capital_fee' => $fees['shared_capital_fee'] ?? 0,
                                    'insurance_fee' => $fees['insurance_fee'] ?? 0,
                                    'processing_fee' => $fees['processing_fee'] ?? 0,
                                    'coop_fee_total' => $fees['coop_fee_total'] ?? 0,
                                    'net_release_amount' => $fees['net_release_amount'] ?? 0,
                                    'interest_rate' => $interestRate,
                                    'term_months' => $term,
                                    'release_date' => $release,
                                    'maturity_date' => now()->addMonths($term)->toDateString(),
                                    'monthly_amortization' => $monthlyAmort,
                                    'balance' => $principal,
                                    'status' => 'Active', // ✅ This is the payable loan
                                ]);

                                $oldLoanAccount->update([
                                    'restructured_to_loan_id' => $newLoan->loan_account_id,
                                ]);
                            });

                            Notification::make()
                                ->title('Restructure approved')
                                ->body('The old loan is now marked Restructured. A new Active loan has been created with the new terms.')
                                ->success()
                                ->send();
                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => in_array($record->status, ['Pending', 'Under Review'], true))
                        ->form([Textarea::make('reason')->required()])
                        ->action(function ($record, array $data) {
                            $from = $record->status;
                            $record->update(['status' => 'Rejected']);

                            RestructureApplicationStatusLog::create([
                                'restructure_application_id' => $record->restructure_application_id,
                                'from_status' => $from,
                                'to_status' => 'Rejected',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Rejected')
                                ->success()
                                ->send();
                        }),

                    Action::make('cancel')
                        ->label('Cancel')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => in_array($record->status, ['Pending', 'Under Review'], true))
                        ->action(function ($record) {
                            $from = $record->status;
                            $record->update(['status' => 'Cancelled']);

                            RestructureApplicationStatusLog::create([
                                'restructure_application_id' => $record->restructure_application_id,
                                'from_status' => $from,
                                'to_status' => 'Cancelled',
                                'changed_by_user_id' => auth()->id(),
                                'changed_at' => now(),
                            ]);

                            Notification::make()
                                ->title('Cancelled')
                                ->success()
                                ->send();
                        }),

                ])
                    ->iconButton()
                    ->tooltip('Actions'),
            ], position: RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
