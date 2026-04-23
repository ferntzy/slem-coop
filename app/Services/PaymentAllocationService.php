<?php

namespace App\Services;

use App\Models\PaymentAllocationRule;

class PaymentAllocationService
{
    public static function allocate(
        float $balance,
        float $rate,
        float $payment,
        float $penalty = 0
    ) {

        // Calculate monthly interest
        $monthlyInterest = $balance * ($rate / 100) / 12;

        // Get allocation rules ordered by priority
        $rules = PaymentAllocationRule::orderBy('priority')->get();

        $remaining = $payment;

        $result = [
            'interest' => 0,
            'principal' => 0,
            'penalty' => 0,
        ];

        foreach ($rules as $rule) {

            if ($rule->component === 'interest') {

                $pay = min($remaining, $monthlyInterest);

                $result['interest'] = $pay;

                $remaining -= $pay;
            }

            if ($rule->component === 'principal') {

                $pay = min($remaining, $balance);

                $result['principal'] = $pay;

                $remaining -= $pay;

                $balance -= $pay;
            }

            if ($rule->component === 'penalty') {

                $pay = min($remaining, $penalty);

                $result['penalty'] = $pay;

                $remaining -= $pay;
            }
        }

        return [

            'interest_paid' => round($result['interest'], 2),

            'principal_paid' => round($result['principal'], 2),

            'penalty_paid' => round($result['penalty'], 2),

            'carry_forward' => round($remaining, 2),

            'new_balance' => round($balance, 2),
        ];
    }
}
