<?php

namespace App\Services;

use App\Models\LoanAccount;
use Carbon\Carbon;

class LoanPenaltyService
{
    public function __construct(
        protected PenaltyCalculatorService $penaltyCalculatorService,
        protected LoanAmortizationService $loanAmortizationService,
    ) {}

    public function calculateForLoanAccount(
        LoanAccount $loanAccount,
        ?string $asOf = null
    ): array {
        if (! $loanAccount->penalty_rule_id) {
            return [
                'loan_account_id' => $loanAccount->loan_account_id,
                'as_of' => $asOf ? Carbon::parse($asOf)->toDateString() : now()->toDateString(),
                'total_overdue_amount' => 0,
                'total_penalty' => 0,
                'grand_total_due' => 0,
                'rows' => [],
            ];
        }

        $schedule = $this->loanAmortizationService->generate(
            loanAmount: (float) $loanAccount->principal_amount,
            monthlyInterestRatePercent: (float) $loanAccount->interest_rate,
            termMonths: (int) $loanAccount->term_months,
            releaseDate: $loanAccount->release_date,
        );

        $asOfDate = $asOf
            ? Carbon::parse($asOf)->startOfDay()
            : Carbon::today();

        $rows = [];
        $totalPenalty = 0;
        $totalOverdueAmount = 0;

        foreach ($schedule as $row) {
            $dueDate = Carbon::parse($row['due_date'])->startOfDay();

            if ($dueDate->gt($asOfDate)) {
                continue;
            }

            $outstandingAmount = (float) $row['amortization'];

            if ($outstandingAmount <= 0) {
                continue;
            }

            $result = $this->penaltyCalculatorService->calculate(
                ruleId: (int) $loanAccount->penalty_rule_id,
                outstandingAmount: $outstandingAmount,
                dueDateString: $row['due_date'],
                asOf: $asOfDate->toDateString(),
            );

            if (($result['penalty_amount'] ?? 0) <= 0) {
                continue;
            }

            $rows[] = [
                'period' => $row['period'],
                'due_date' => $row['due_date'],
                'due_date_formatted' => $row['due_date_formatted'],
                'beginning_balance' => $row['beginning_balance'],
                'principal' => $row['principal'],
                'interest' => $row['interest'],
                'amortization' => $row['amortization'],
                'ending_balance' => $row['ending_balance'],
                'overdue_days' => $result['overdue_days'],
                'grace_period_days' => $result['grace_period_days'],
                'effective_rate' => $result['effective_rate'],
                'penalty_amount' => $result['penalty_amount'],
                'total_due' => $result['total_due'],
            ];

            $totalPenalty += (float) $result['penalty_amount'];
            $totalOverdueAmount += $outstandingAmount;
        }

        return [
            'loan_account_id' => $loanAccount->loan_account_id,
            'as_of' => $asOfDate->toDateString(),
            'total_overdue_amount' => round($totalOverdueAmount, 2),
            'total_penalty' => round($totalPenalty, 2),
            'grand_total_due' => round($totalOverdueAmount + $totalPenalty, 2),
            'rows' => $rows,
        ];
    }
}
