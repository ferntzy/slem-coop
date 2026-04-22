<?php

namespace App\Console\Commands;

use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use App\Services\SavingsInterestService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ApplyQuarterlySavingsInterest extends Command
{
    protected $signature = 'savings:apply-quarterly-interest {--quarter=}';

    protected $description = 'Apply quarterly ADB-based interest to regular savings accounts';

    public function __construct(
        protected SavingsInterestService $savingsInterestService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        [$periodStart, $periodEnd] = $this->resolvePeriod();

        $regularSavingsType = SavingsType::query()
            ->where('name', 'Regular Savings')
            ->orWhere('code', 'SA 02')
            ->first();

        if (! $regularSavingsType) {
            $this->error('Regular Savings type not found.');

            return self::FAILURE;
        }

        $profileIds = SavingsAccountTransaction::query()
            ->where('savings_type_id', (string) $regularSavingsType->getKey())
            ->whereDate('transaction_date', '<=', $periodEnd->toDateString())
            ->distinct()
            ->pluck('profile_id');

        foreach ($profileIds as $profileId) {
            $interestAmount = $this->savingsInterestService->computeInterest(
                (int) $profileId,
                $regularSavingsType,
                $periodStart,
                $periodEnd
            );

            if ($interestAmount <= 0) {
                continue;
            }

            if ($this->interestAlreadyApplied((int) $profileId, $regularSavingsType, $periodStart, $periodEnd)) {
                $this->line("Interest already applied for profile {$profileId}.");

                continue;
            }

            $this->storeInterestTransaction(
                (int) $profileId,
                $regularSavingsType,
                $interestAmount,
                $periodEnd
            );

            $this->info("Applied savings interest for profile {$profileId}: {$interestAmount}");
        }

        return self::SUCCESS;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolvePeriod(): array
    {
        $quarterOption = $this->option('quarter');

        if (filled($quarterOption)) {
            $quarterDate = Carbon::createFromFormat('Y-m', (string) $quarterOption)->startOfMonth();
        } else {
            $quarterDate = now()->subQuarter()->startOfQuarter();
        }

        return [$quarterDate->copy()->startOfQuarter(), $quarterDate->copy()->endOfQuarter()];
    }

    protected function interestAlreadyApplied(
        int $profileId,
        SavingsType $savingsType,
        Carbon $periodStart,
        Carbon $periodEnd
    ): bool {
        return SavingsAccountTransaction::query()
            ->where('profile_id', $profileId)
            ->where('savings_type_id', (string) $savingsType->getKey())
            ->where('type', 'Interest')
            ->whereDate('transaction_date', '>=', $periodStart->toDateString())
            ->whereDate('transaction_date', '<=', $periodEnd->toDateString())
            ->exists();
    }

    protected function storeInterestTransaction(
        int $profileId,
        SavingsType $savingsType,
        float $interestAmount,
        Carbon $transactionDate
    ): void {
        DB::transaction(function () use ($profileId, $savingsType, $interestAmount, $transactionDate): void {
            SavingsAccountTransaction::create([
                'profile_id' => $profileId,
                'savings_type_id' => (string) $savingsType->getKey(),
                'deposit' => $interestAmount,
                'type' => 'Interest',
                'status' => 'completed',
                'transaction_date' => $transactionDate->copy()->endOfDay(),
                'notes' => 'Quarterly ADB interest',
            ]);
        });
    }
}
