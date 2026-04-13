<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationsResource;
use App\Services\CoopFeeCalculatorService;
use Filament\Resources\Pages\EditRecord;

class EditLoanApplications extends EditRecord
{
    protected static string $resource = LoanApplicationsResource::class;

    public function getTitle(): string
    {
        return 'Edit ' . $this->record->member?->profile?->first_name;
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
            ? rtrim(rtrim((string) $record->type->max_interest_rate, '0'), '.') . '%'
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