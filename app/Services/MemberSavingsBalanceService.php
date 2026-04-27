<?php

namespace App\Services;

use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MemberSavingsBalanceService
{
    /**
     * @var array<string, Collection<int, SavingsAccountTransaction>>
     */
    protected array $transactionsCache = [];

    protected ?SavingsType $regularSavingsType = null;

    /**
     * @var array<int, string>|null
     */
    protected ?array $transactionalSavingsTypeIds = null;

    public function getRegularSavingsBalance(int $profileId): float
    {
        $transactions = $this->getRegularSavingsTransactions($profileId);

        return round($transactions->sum(function (SavingsAccountTransaction $transaction): float {
            return $this->transactionDepositAmount($transaction) - $this->transactionWithdrawalAmount($transaction);
        }), 2);
    }

    /**
     * @return Collection<int, SavingsAccountTransaction>
     */
    protected function getRegularSavingsTransactions(int $profileId): Collection
    {
        $transactionalSavingsTypeIds = $this->getTransactionalSavingsTypeIds();

        if (! $profileId || $transactionalSavingsTypeIds === []) {
            return collect();
        }

        $cacheKey = $profileId.':'.implode(',', $transactionalSavingsTypeIds);

        return $this->transactionsCache[$cacheKey] ??= SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->whereIn('savings_type_id', $transactionalSavingsTypeIds)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return array<int, string>
     */
    protected function getTransactionalSavingsTypeIds(): array
    {
        if ($this->transactionalSavingsTypeIds !== null) {
            return $this->transactionalSavingsTypeIds;
        }

        $query = SavingsType::query();

        if (Schema::hasColumn('savings_types', 'is_active')) {
            $query->where('is_active', true);
        }

        $hasDepositAllowed = Schema::hasColumn('savings_types', 'deposit_allowed');
        $hasWithdrawalAllowed = Schema::hasColumn('savings_types', 'withdrawal_allowed');

        if ($hasDepositAllowed && $hasWithdrawalAllowed) {
            $query
                ->where('deposit_allowed', true)
                ->where('withdrawal_allowed', true);
        }

        $this->transactionalSavingsTypeIds = $query
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        return $this->transactionalSavingsTypeIds;
    }

    protected function getRegularSavingsType(): ?SavingsType
    {
        if ($this->regularSavingsType !== null) {
            return $this->regularSavingsType;
        }

        $this->regularSavingsType = SavingsType::query()
            ->where('name', 'Regular Savings')
            ->orWhere('code', 'SA 02')
            ->first();

        return $this->regularSavingsType;
    }

    protected function transactionDepositAmount(SavingsAccountTransaction $transaction): float
    {
        $depositAmount = (float) ($transaction->deposit ?? 0);

        if ($depositAmount > 0) {
            return round($depositAmount, 2);
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['deposit', 'credit'], true) || in_array($direction, ['credit', 'inflow'], true)) {
            return round((float) ($transaction->amount ?? 0), 2);
        }

        return 0.0;
    }

    protected function transactionWithdrawalAmount(SavingsAccountTransaction $transaction): float
    {
        $withdrawalAmount = (float) ($transaction->withdrawal ?? 0);

        if ($withdrawalAmount > 0) {
            return round($withdrawalAmount, 2);
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['withdrawal', 'debit'], true) || in_array($direction, ['debit', 'outflow'], true)) {
            return round((float) ($transaction->amount ?? 0), 2);
        }

        return 0.0;
    }
}
