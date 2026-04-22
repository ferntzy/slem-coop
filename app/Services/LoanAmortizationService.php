<?php

namespace App\Services;

use Carbon\Carbon;
use DateTimeInterface;

class LoanAmortizationService
{
    public function generate(
        float $loanAmount,
        float $monthlyInterestRatePercent,
        int $termMonths,
        string|DateTimeInterface $releaseDate
    ): array {
        $rows = [];

        $releaseDate = Carbon::parse($releaseDate)->startOfDay();
        $monthlyRate = $monthlyInterestRatePercent / 100;

        $monthlyPrincipal = round($loanAmount / $termMonths, 2);
        $balance = round($loanAmount, 2);

        for ($i = 1; $i <= $termMonths; $i++) {
            $dueDate = $releaseDate->copy()->addMonths($i);
            $beginningBalance = $balance;

            $interest = round($beginningBalance * $monthlyRate, 2);

            $principal = $i === $termMonths
                ? $beginningBalance
                : $monthlyPrincipal;

            $amortization = round($principal + $interest, 2);
            $endingBalance = round($beginningBalance - $principal, 2);

            if ($endingBalance < 0) {
                $endingBalance = 0;
            }

            $rows[] = [
                'period' => $i,
                'month' => $i,
                'due_date' => $dueDate->toDateString(),
                'due_date_formatted' => $dueDate->format('m/d/Y'),
                'beginning_balance' => round($beginningBalance, 2),
                'interest' => round($interest, 2),
                'principal' => round($principal, 2),
                'amortization' => round($amortization, 2),
                'ending_balance' => round($endingBalance, 2),
                'balance' => round($endingBalance, 2),
            ];

            $balance = $endingBalance;
        }

        return $rows;
    }
}
