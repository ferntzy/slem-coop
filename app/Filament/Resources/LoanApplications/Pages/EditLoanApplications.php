<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationsResource;
use App\Models\CoopSetting;
use App\Models\LoanApplicationStatusLog;
use App\Services\CoopFeeCalculatorService;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLoanApplications extends EditRecord
{
    protected static string $resource = LoanApplicationsResource::class;

    protected function loanOfficerApprovalLimit(): float
    {
        return (float) CoopSetting::get('loan.loan_officer_approval_limit', 20000);
    }

    public function getTitle(): string
    {
        return 'Edit '.$this->record->member?->profile?->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => in_array($this->record->status, ['Pending', 'Under Review']) && ((auth()->user()?->canApproveAnyLoanAmount() ?? false) || auth()->user()?->hasAnyRole(['Account Officer', 'Loan Officer', 'HQ Loan Officer', 'hq_loan_officer'])))
                ->action(function (): void {
                    $user = auth()->user();
                    $notificationService = app(NotificationService::class);
                    $approvalLimit = $this->loanOfficerApprovalLimit();
                    $requiresDualApproval = (float) $this->record->amount_requested > $approvalLimit;
                    $canApproveAnyLoanAmount = $user?->canApproveAnyLoanAmount() ?? false;
                    $isLoanOfficerApprover = $user?->hasAnyRole([
                        'Loan Officer',
                        'loan_officer',
                        'HQ Loan Officer',
                        'hq_loan_officer',
                    ]) ?? false;

                    if ($requiresDualApproval && ! $canApproveAnyLoanAmount) {
                        if (! $isLoanOfficerApprover) {
                            Notification::make()
                                ->danger()
                                ->title('Unauthorized for high-value approval')
                                ->send();

                            return;
                        }

                        $from = $this->record->status;
                        $this->record->update(['status' => 'Under Review']);

                        if ($from !== 'Under Review') {
                            LoanApplicationStatusLog::create([
                                'loan_application_id' => $this->record->loan_application_id,
                                'from_status' => $from,
                                'to_status' => 'Under Review',
                                'changed_by_user_id' => auth()->id(),
                                'reason' => 'Escalated for Manager and Admin approvals due to loan officer limit.',
                                'changed_at' => now(),
                            ]);
                        }

                        $notificationService->notifyManagers(
                            'Loan requires manager approval',
                            "Loan application #{$this->record->loan_application_id} exceeds the loan officer limit of PHP ".number_format($approvalLimit, 2).' and needs manager approval.',
                            notifiableType: 'loan_application',
                            notifiableId: $this->record->loan_application_id
                        );

                        $notificationService->notifyAdmins(
                            'Loan requires admin approval',
                            "Loan application #{$this->record->loan_application_id} exceeds the loan officer limit of PHP ".number_format($approvalLimit, 2).' and needs manager + admin approvals.',
                            notifiableType: 'loan_application',
                            notifiableId: $this->record->loan_application_id
                        );

                        Notification::make()
                            ->warning()
                            ->title('Escalated for manager and admin approvals')
                            ->send();

                        return;
                    }

                    $this->record->update([
                        'status' => 'Approved',
                        'approved_at' => now(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Loan Approved')
                        ->send();
                }),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (): bool => auth()->user()?->hasAnyRole(['super_admin', 'Admin', 'Manager', 'Account Officer', 'Loan Officer', 'HQ Loan Officer', 'hq_loan_officer']) && in_array($this->record->status, ['Pending', 'Under Review']))
                ->action(function (): void {
                    $this->record->update([
                        'status' => 'Rejected',
                        'approved_at' => null,
                        'manager_approved_at' => null,
                        'manager_approved_by_user_id' => null,
                        'admin_approved_at' => null,
                        'admin_approved_by_user_id' => null,
                    ]);
                    Notification::make()
                        ->danger()
                        ->title('Loan Rejected')
                        ->send();
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->record;

        $data['salary'] = $record->getCashflowAmount('salary', 'income');
        $data['business_income'] = $record->getCashflowAmount('business_income', 'income');
        $data['remittances'] = $record->getCashflowAmount('remittances', 'income');
        $data['other_income'] = $record->getCashflowAmount('other_income', 'income');

        $data['living_expenses'] = $record->getCashflowAmount('living_expenses', 'expense');
        $data['business_expenses'] = $record->getCashflowAmount('business_expenses', 'expense');
        $data['existing_loan_payments'] = $record->getCashflowAmount('existing_loan_payments', 'expense');
        $data['other_expenses'] = $record->getCashflowAmount('other_expenses', 'expense');

        $data['interest_rate_display'] = filled($record->type?->max_interest_rate)
            ? rtrim(rtrim((string) $record->type->max_interest_rate, '0'), '.').'%'
            : null;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $amountRequested = (float) ($data['amount_requested'] ?? 0);
        $fees = app(CoopFeeCalculatorService::class)
            ->calculate('loan_application', $amountRequested);

        unset(
            $data['salary'],
            $data['business_income'],
            $data['remittances'],
            $data['other_income'],
            $data['living_expenses'],
            $data['business_expenses'],
            $data['existing_loan_payments'],
            $data['other_expenses'],
            $data['interest_rate_display']
        );

        $data['shared_capital_fee'] = $fees['shared_capital_fee'] ?? 0;
        $data['insurance_fee'] = $fees['insurance_fee'] ?? 0;
        $data['processing_fee'] = $fees['processing_fee'] ?? 0;
        $data['coop_fee_total'] = $fees['coop_fee_total'] ?? 0;
        $data['net_release_amount'] = $fees['net_release_amount'] ?? 0;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncCashflowsFromForm($this->form->getState());
    }
}
