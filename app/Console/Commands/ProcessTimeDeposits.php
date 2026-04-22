<?php

namespace App\Console\Commands;

use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use App\Services\SavingsInterestService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessTimeDeposits extends Command
{
    protected const ACTION_TRANSFER_TO_SAVINGS = 'transfer_to_savings';

    protected const ACTION_RENEW_TIME_DEPOSIT = 'renew_time_deposit';

    protected $signature = 'process:timedeposits';

    protected $description = 'Process matured time deposits';

    public function __construct(
        protected SavingsInterestService $savingsInterestService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $timeDepositType = SavingsType::query()
            ->where('name', 'Time Deposit')
            ->orWhere('code', 'SA 01')
            ->first();

        $regularSavingsType = SavingsType::query()
            ->where('name', 'Regular Savings')
            ->orWhere('code', 'SA 02')
            ->first();

        if (! $timeDepositType || ! $regularSavingsType) {
            $this->error('Required savings types were not found.');

            return self::FAILURE;
        }

        $deposits = SavingsAccountTransaction::query()
            ->where('status', 'ongoing')
            ->where('type', 'Deposit')
            ->where('savings_type_id', $timeDepositType->getKey())
            ->whereNotNull('transaction_date')
            ->whereNotNull('terms')
            ->get();

        foreach ($deposits as $deposit) {
            $maturityDate = $deposit->transaction_date->copy()->addMonths((int) $deposit->terms);

            if (now()->lt($maturityDate)) {
                continue;
            }

            $interestAmount = $this->savingsInterestService->computeTimeDepositInterest(
                $deposit,
                $timeDepositType
            );

            DB::transaction(function () use ($deposit, $interestAmount, $regularSavingsType, $timeDepositType): void {
                $deposit->update([
                    'status' => 'completed',
                ]);

                $maturityAction = $deposit->maturity_action ?: self::ACTION_TRANSFER_TO_SAVINGS;
                $maturityAmount = round((float) $deposit->deposit + $interestAmount, 2);

                if ($maturityAction === self::ACTION_RENEW_TIME_DEPOSIT) {
                    SavingsAccountTransaction::create([
                        'profile_id' => $deposit->profile_id,
                        'savings_type_id' => (string) $timeDepositType->getKey(),
                        'deposit' => $maturityAmount,
                        'type' => 'Deposit',
                        'status' => 'ongoing',
                        'terms' => $deposit->terms,
                        'transaction_date' => now(),
                        'notes' => 'Renewed from matured time deposit #'.$deposit->id.' with interest',
                    ]);

                    return;
                }

                SavingsAccountTransaction::create([
                    'profile_id' => $deposit->profile_id,
                    'savings_type_id' => (string) $regularSavingsType->getKey(),
                    'deposit' => $maturityAmount,
                    'type' => 'Deposit',
                    'status' => 'completed',
                    'transaction_date' => now(),
                    'notes' => 'Transferred from matured time deposit #'.$deposit->id.' with interest',
                ]);
            });

            $this->info('Processed time deposit ID: '.$deposit->id);
        }

        return self::SUCCESS;
    }
}
