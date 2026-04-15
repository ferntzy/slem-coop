<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationsResource;
use App\Models\MemberDetail;
use App\Services\CoopFeeCalculatorService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateLoanApplications extends CreateRecord
{
    protected static string $resource = LoanApplicationsResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

        if (Auth::user()?->isMember()) {
            $data['member_id'] = MemberDetail::where('profile_id', Auth::user()->profile_id)->value('id');
        }

        $data['shared_capital_fee'] = $fees['shared_capital_fee'] ?? 0;
        $data['insurance_fee'] = $fees['insurance_fee'] ?? 0;
        $data['processing_fee'] = $fees['processing_fee'] ?? 0;
        $data['coop_fee_total'] = $fees['coop_fee_total'] ?? 0;
        $data['net_release_amount'] = $fees['net_release_amount'] ?? 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncCashflowsFromForm($this->form->getState());
    }
}
