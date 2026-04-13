<?php

namespace App\Services;

use App\Models\LoanAccount;

class LoanAccountBalanceService
{
    public function __construct(
        protected LoanScheduleService $loanScheduleService
    ) {}

    public function update(LoanAccount $loanAccount): void
    {
        $schedule = $this->loanScheduleService->build($loanAccount);

        $totalPrincipalPaid = collect($schedule)->sum('paid_principal');

        $loanAmount = (float) $loanAccount->principal_amount;
        $newBalance = max($loanAmount - $totalPrincipalPaid, 0);

        $loanAccount->update([
            'balance' => round($newBalance, 2),
            'status' => $newBalance <= 0 ? 'Completed' : 'Active',
        ]);
    }
}