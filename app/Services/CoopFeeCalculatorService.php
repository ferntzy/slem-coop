<?php

namespace App\Services;

use App\Models\CoopFee;

class CoopFeeCalculatorService
{
    public function calculate(string $processKey, float $baseAmount): array
    {
        $fees = CoopFee::query()
            ->active()
            ->whereHas('feeType', fn ($query) => $query->where('key', $processKey))
            ->get();

        $breakdown = [];
        $total = 0;

        foreach ($fees as $fee) {
            $computedAmount = $fee->is_percentage
                ? round($baseAmount * ((float) $fee->percentage / 100), 2)
                : round((float) $fee->amount, 2);

            $breakdown[] = [
                'type' => $fee->type,
                'name' => $fee->name ?: $fee->typeLabel,
                'amount' => $computedAmount,
                'is_percentage' => (bool) $fee->is_percentage,
                'value' => $fee->is_percentage
                    ? (float) $fee->percentage
                    : (float) $fee->amount,
            ];

            $total += $computedAmount;
        }

        $mapped = collect($breakdown)->keyBy('type');

        return [
            'shared_capital_fee' => (float) ($mapped['shared_capital']['amount'] ?? 0),
            'insurance_fee' => (float) ($mapped['insurance']['amount'] ?? 0),
            'processing_fee' => (float) ($mapped['processing_fee']['amount'] ?? 0),

            'coop_fee_total' => round($total, 2),
            'net_release_amount' => round(max($baseAmount - $total, 0), 2),
            'coop_fee_breakdown' => $breakdown,
        ];
    }
}
