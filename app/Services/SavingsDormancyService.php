<?php

namespace App\Services;

use App\Models\CoopSetting;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SavingsDormancyService
{
    private const DIRECTION_DEPOSIT = 'deposit';

    private const DIRECTION_WITHDRAWAL = 'withdrawal';

    private const DIRECTION_TRANSFER = 'transfer';

    private const DIRECTION_SYSTEM_INTEREST = 'system_interest';

    private const DIRECTION_SYSTEM_DORMANCY_FEE = 'system_dormancy_fee';

    public function processMonthly(?Carbon $processingDate = null): array
    {
        $processingDate ??= now();

        $settings = $this->getSettings();

        $transactionalSavingsTypeIds = SavingsType::query()
            ->where('is_active', true)
            ->where('deposit_allowed', true)
            ->where('withdrawal_allowed', true)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        if ($transactionalSavingsTypeIds === []) {
            return [
                'evaluated_accounts' => 0,
                'dormant_accounts' => 0,
                'interest_posted' => 0,
                'dormancy_fees_posted' => 0,
            ];
        }

        $savingsTypes = SavingsType::query()
            ->whereIn('id', $transactionalSavingsTypeIds)
            ->get()
            ->keyBy(fn (SavingsType $savingsType): string => (string) $savingsType->id);

        $accounts = $this->getAccountsWithPositiveBalance($transactionalSavingsTypeIds);

        $evaluatedAccounts = 0;
        $dormantAccounts = 0;
        $interestPosted = 0;
        $dormancyFeesPosted = 0;

        foreach ($accounts as $account) {
            $profileId = (int) $account->profile_id;
            $savingsTypeId = (string) $account->savings_type_id;
            $currentBalance = round((float) $account->balance, 2);
            $savingsType = $savingsTypes->get($savingsTypeId);

            if (! $savingsType || $currentBalance <= 0) {
                continue;
            }

            $evaluatedAccounts++;

            $lastCustomerInitiatedTransactionDate = $this->getLastCustomerInitiatedTransactionDate($profileId, $savingsTypeId);
            $isDormant = $this->isDormant(
                $lastCustomerInitiatedTransactionDate,
                $settings['dormancy_months_threshold'],
                $processingDate,
            );

            if ($isDormant) {
                $dormantAccounts++;
            }

            if (
                (! $isDormant || $settings['apply_interest_on_dormant'])
                && $this->canPostMonthly($profileId, $savingsTypeId, self::DIRECTION_SYSTEM_INTEREST, $processingDate)
            ) {
                $interestAmount = $this->calculateMonthlyInterest(
                    $currentBalance,
                    (float) ($savingsType->interest_rate ?? 0),
                );

                if ($interestAmount > 0) {
                    $this->createSystemInterestTransaction(
                        profileId: $profileId,
                        savingsTypeId: $savingsTypeId,
                        amount: $interestAmount,
                        processingDate: $processingDate,
                    );

                    $currentBalance += $interestAmount;
                    $interestPosted++;
                }
            }

            if (
                $isDormant
                && $settings['auto_apply_dormancy_fee']
                && $settings['dormancy_fee_amount'] > 0
                && $this->canPostMonthly($profileId, $savingsTypeId, self::DIRECTION_SYSTEM_DORMANCY_FEE, $processingDate)
            ) {
                $dormancyFeeAmount = $this->calculateDormancyFee(
                    currentBalance: $currentBalance,
                    configuredFee: $settings['dormancy_fee_amount'],
                    maintainingBalance: (float) ($savingsType->maintaining_balance ?? 0),
                );

                if ($dormancyFeeAmount > 0) {
                    $this->createSystemDormancyFeeTransaction(
                        profileId: $profileId,
                        savingsTypeId: $savingsTypeId,
                        amount: $dormancyFeeAmount,
                        processingDate: $processingDate,
                    );

                    $dormancyFeesPosted++;
                }
            }
        }

        return [
            'evaluated_accounts' => $evaluatedAccounts,
            'dormant_accounts' => $dormantAccounts,
            'interest_posted' => $interestPosted,
            'dormancy_fees_posted' => $dormancyFeesPosted,
        ];
    }

    /**
     * @return array{dormancy_months_threshold: int, dormancy_fee_amount: float, auto_apply_dormancy_fee: bool, apply_interest_on_dormant: bool}
     */
    private function getSettings(): array
    {
        return [
            'dormancy_months_threshold' => max((int) CoopSetting::get('savings.dormancy_months_threshold', 24), 1),
            'dormancy_fee_amount' => max((float) CoopSetting::get('savings.dormancy_fee_amount', 30.00), 0),
            'auto_apply_dormancy_fee' => (bool) CoopSetting::get('savings.auto_apply_dormancy_fee', true),
            'apply_interest_on_dormant' => (bool) CoopSetting::get('savings.apply_interest_on_dormant', true),
        ];
    }

    private function getAccountsWithPositiveBalance(array $savingsTypeIds): Collection
    {
        return SavingsAccountTransaction::query()
            ->selectRaw('profile_id, savings_type_id, SUM(COALESCE(deposit, 0) - COALESCE(withdrawal, 0)) as balance')
            ->whereNotNull('profile_id')
            ->whereNotNull('savings_type_id')
            ->whereIn('savings_type_id', $savingsTypeIds)
            ->groupBy('profile_id', 'savings_type_id')
            ->havingRaw('SUM(COALESCE(deposit, 0) - COALESCE(withdrawal, 0)) > 0')
            ->get();
    }

    private function getLastCustomerInitiatedTransactionDate(int $profileId, string $savingsTypeId): ?Carbon
    {
        $lastCustomerDate = SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', $savingsTypeId)
            ->where(function ($query): void {
                $query->whereIn('direction', [
                    self::DIRECTION_DEPOSIT,
                    self::DIRECTION_WITHDRAWAL,
                    self::DIRECTION_TRANSFER,
                ])->orWhere(function ($legacyQuery): void {
                    $legacyQuery
                        ->whereNull('direction')
                        ->whereRaw('LOWER(type) in (?, ?, ?)', ['deposit', 'withdrawal', 'transfer']);
                });
            })
            ->selectRaw('COALESCE(transaction_date, DATE(created_at)) as effective_transaction_date')
            ->orderByRaw('COALESCE(transaction_date, created_at) DESC')
            ->orderByDesc('id')
            ->value('effective_transaction_date');

        if ($lastCustomerDate) {
            return Carbon::parse($lastCustomerDate);
        }

        $fallbackDate = SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', $savingsTypeId)
            ->selectRaw('COALESCE(transaction_date, DATE(created_at)) as effective_transaction_date')
            ->orderByRaw('COALESCE(transaction_date, created_at) DESC')
            ->orderByDesc('id')
            ->value('effective_transaction_date');

        return $fallbackDate ? Carbon::parse($fallbackDate) : null;
    }

    private function isDormant(?Carbon $lastCustomerTransactionDate, int $monthsThreshold, Carbon $processingDate): bool
    {
        if (! $lastCustomerTransactionDate) {
            return false;
        }

        $cutoffDate = $processingDate->copy()->subMonths($monthsThreshold)->startOfDay();

        return $lastCustomerTransactionDate->copy()->startOfDay()->lessThanOrEqualTo($cutoffDate);
    }

    private function canPostMonthly(int $profileId, string $savingsTypeId, string $direction, Carbon $processingDate): bool
    {
        return ! SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', $savingsTypeId)
            ->where('direction', $direction)
            ->whereDate('transaction_date', '>=', $processingDate->copy()->startOfMonth()->toDateString())
            ->whereDate('transaction_date', '<=', $processingDate->copy()->endOfMonth()->toDateString())
            ->exists();
    }

    private function calculateMonthlyInterest(float $currentBalance, float $annualInterestRatePercent): float
    {
        if ($currentBalance <= 0 || $annualInterestRatePercent <= 0) {
            return 0;
        }

        return round(($currentBalance * ($annualInterestRatePercent / 100)) / 12, 2);
    }

    private function calculateDormancyFee(float $currentBalance, float $configuredFee, float $maintainingBalance): float
    {
        $minimumBalanceFloor = max($maintainingBalance, 0);
        $availableForFee = round(max($currentBalance - $minimumBalanceFloor, 0), 2);

        if ($availableForFee <= 0 || $configuredFee <= 0) {
            return 0;
        }

        return round(min($configuredFee, $availableForFee), 2);
    }

    private function createSystemInterestTransaction(int $profileId, string $savingsTypeId, float $amount, Carbon $processingDate): void
    {
        SavingsAccountTransaction::create([
            'profile_id' => $profileId,
            'savings_type_id' => $savingsTypeId,
            'type' => 'Interest',
            'direction' => self::DIRECTION_SYSTEM_INTEREST,
            'deposit' => $amount,
            'amount' => $amount,
            'status' => 'completed',
            'transaction_date' => $processingDate->toDateString(),
            'notes' => 'System monthly savings interest credit',
            'posted_by_user_id' => null,
        ]);
    }

    private function createSystemDormancyFeeTransaction(int $profileId, string $savingsTypeId, float $amount, Carbon $processingDate): void
    {
        SavingsAccountTransaction::create([
            'profile_id' => $profileId,
            'savings_type_id' => $savingsTypeId,
            'type' => 'Dormancy Fee',
            'direction' => self::DIRECTION_SYSTEM_DORMANCY_FEE,
            'withdrawal' => $amount,
            'amount' => $amount,
            'status' => 'completed',
            'transaction_date' => $processingDate->toDateString(),
            'notes' => 'System monthly dormancy fee charge',
            'posted_by_user_id' => null,
        ]);
    }
}
