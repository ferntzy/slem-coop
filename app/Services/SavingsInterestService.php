<?php

namespace App\Services;

use App\Models\CoopSetting;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SavingsInterestService
{
    public function computeADB(
        int $profileId,
        SavingsType $savingsType,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        $periodStart = $startDate->copy()->startOfDay();
        $periodEnd = $endDate->copy()->startOfDay();

        if ($periodEnd->lt($periodStart)) {
            return 0.0;
        }

        /** @var Collection<int, SavingsAccountTransaction> $transactions */
        $transactions = SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', (string) $savingsType->getKey())
            ->whereDate('transaction_date', '<=', $periodEnd->toDateString())
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $runningBalance = 0.0;
        $weightedBalanceTotal = 0.0;
        $cursorDate = $periodStart->copy();

        foreach ($transactions as $transaction) {
            $transactionDate = $transaction->transaction_date->copy()->startOfDay();
            $transactionImpact = $this->getTransactionImpact($transaction);

            if ($transactionDate->lt($periodStart)) {
                $runningBalance += $transactionImpact;

                continue;
            }

            $daysHeld = $cursorDate->diffInDays($transactionDate);

            if ($daysHeld > 0) {
                $weightedBalanceTotal += $runningBalance * $daysHeld;
            }

            $runningBalance += $transactionImpact;
            $cursorDate = $transactionDate->copy();
        }

        $daysRemaining = $cursorDate->diffInDays($periodEnd->copy()->addDay());

        if ($daysRemaining > 0) {
            $weightedBalanceTotal += $runningBalance * $daysRemaining;
        }

        $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;

        if ($daysInPeriod <= 0) {
            return 0.0;
        }

        return round($weightedBalanceTotal / $daysInPeriod, 2);
    }

    public function computeInterest(
        int $profileId,
        SavingsType $savingsType,
        Carbon $startDate,
        Carbon $endDate
    ): float {
        $adb = $this->computeADB($profileId, $savingsType, $startDate, $endDate);
        $minimumBalance = (float) ($savingsType->maintaining_balance ?? 0);

        if ($adb < $minimumBalance) {
            return 0.0;
        }

        $annualRate = $this->resolveAnnualRatePercent($savingsType) / 100;
        $daysInPeriod = $startDate->copy()->startOfDay()->diffInDays($endDate->copy()->startOfDay()) + 1;

        if ($annualRate <= 0 || $daysInPeriod <= 0) {
            return 0.0;
        }

        return round($adb * $annualRate * ($daysInPeriod / 365), 2);
    }

    public function computeTimeDepositInterest(
        SavingsAccountTransaction $transaction,
        SavingsType $savingsType
    ): float {
        $principal = (float) ($transaction->deposit ?? 0);
        $termMonths = (int) ($transaction->terms ?? 0);
        $annualRate = $this->resolveAnnualRatePercent($savingsType) / 100;

        if ($principal <= 0 || $termMonths <= 0 || $annualRate <= 0) {
            return 0.0;
        }

        return round($principal * $annualRate * ($termMonths / 12), 2);
    }

    public function resolveAnnualRatePercent(SavingsType $savingsType): float
    {
        $settingKey = match (true) {
            $this->isRegularSavings($savingsType) => 'savings.regular_interest_rate_percent',
            $this->isTimeDeposit($savingsType) => 'savings.time_deposit_interest_rate_percent',
            default => null,
        };

        if (! $settingKey) {
            return (float) ($savingsType->interest_rate ?? 0);
        }

        return (float) CoopSetting::get($settingKey, (float) ($savingsType->interest_rate ?? 0));
    }

    protected function isRegularSavings(SavingsType $savingsType): bool
    {
        return $savingsType->name === 'Regular Savings' || $savingsType->code === 'SA 02';
    }

    protected function isTimeDeposit(SavingsType $savingsType): bool
    {
        return $savingsType->name === 'Time Deposit' || $savingsType->code === 'SA 01';
    }

    protected function getTransactionImpact(SavingsAccountTransaction $transaction): float
    {
        $depositAmount = (float) ($transaction->deposit ?? 0);
        $withdrawalAmount = (float) ($transaction->withdrawal ?? 0);

        return $depositAmount - $withdrawalAmount;
    }
}
