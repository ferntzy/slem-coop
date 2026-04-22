<?php

namespace App\Services;

use App\Models\PenaltyRule;
use Carbon\Carbon;

class PenaltyCalculatorService
{
    /**
     * Calculate penalty using a specific rule.
     *
     * @param  int  $ruleId  PenaltyRule ID to use
     * @param  float  $outstandingAmount  The overdue loan amount
     * @param  string  $dueDateString  The original due date (Y-m-d)
     * @param  string|null  $asOf  Calculate as of this date (defaults to today)
     * @return array{
     *     rule: PenaltyRule,
     *     outstanding_amount: float,
     *     due_date: string,
     *     as_of: string,
     *     overdue_days: int,
     *     grace_period_days: int,
     *     effective_rate: float,
     *     penalty_amount: float,
     *     total_due: float,
     * }
     */
    public function calculate(
        int $ruleId,
        float $outstandingAmount,
        string $dueDateString,
        ?string $asOf = null
    ): array {
        $rule = PenaltyRule::findOrFail($ruleId);
        $dueDate = Carbon::parse($dueDateString)->startOfDay();
        $asOfDate = $asOf ? Carbon::parse($asOf)->startOfDay() : Carbon::today();

        $overdueDays = max(0, $dueDate->diffInDays($asOfDate, false));
        $gracePeriodDays = (int) ($rule->grace_period_days ?? 0);
        $effectiveOverdueDays = max(0, $overdueDays - $gracePeriodDays);
        $effectiveRate = $rule->effectiveRate($effectiveOverdueDays);
        $penaltyAmount = $rule->calculate($outstandingAmount, $overdueDays);

        return [
            'rule' => $rule,
            'outstanding_amount' => $outstandingAmount,
            'due_date' => $dueDate->toDateString(),
            'as_of' => $asOfDate->toDateString(),
            'overdue_days' => $overdueDays,
            'grace_period_days' => $gracePeriodDays,
            'effective_rate' => $effectiveRate,
            'penalty_amount' => $penaltyAmount,
            'total_due' => round($outstandingAmount + $penaltyAmount, 2),
        ];
    }

    /**
     * Calculate penalties for multiple rules and return a breakdown.
     */
    public function calculateAll(
        float $outstandingAmount,
        string $dueDateString,
        ?string $asOf = null
    ): array {
        $rules = PenaltyRule::where('status', 'active')->get();

        return $rules->map(fn ($rule) => $this->calculate($rule->id, $outstandingAmount, $dueDateString, $asOf)
        )->toArray();
    }
}
