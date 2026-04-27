<?php

namespace App\Console\Commands;

use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendTimeDepositMaturityReminders extends Command
{
    protected $signature = 'reminders:time-deposit-maturity';
    protected $description = 'Send SMS and in-app reminders to members with time deposits maturing in 7 days';

    public function handle(NotificationService $notificationService): void
    {
        $timeDepositType = SavingsType::where('name', 'Time Deposit')
            ->orWhere('code', 'SA 01')
            ->first();

        if (! $timeDepositType) {
            $this->error('Time deposit savings type not found.');
            return;
        }

        // Get all ongoing time deposits
        $transactions = SavingsAccountTransaction::query()
            ->where('savings_type_id', (string) $timeDepositType->getKey())
            ->where('type', 'Deposit')
            ->where('status', 'ongoing')
            ->with('member')
            ->get();

        $count = 0;

        foreach ($transactions as $transaction) {
            if (! $transaction->transaction_date || ! $transaction->terms) {
                continue;
            }

            $maturityDate = $transaction->transaction_date
                ->copy()
                ->addMonths((int) $transaction->terms)
                ->startOfDay();

            $daysUntilMaturity = now()->startOfDay()->diffInDays($maturityDate, false);

            // Only notify exactly 7 days before
            if ($daysUntilMaturity !== 7) {
                continue;
            }

            $profile = $transaction->member;

            if (! $profile) {
                continue;
            }

            $amount = (float) ($transaction->deposit ?? 0);
            $formattedAmount = '₱' . number_format($amount, 2);
            $formattedDate = $maturityDate->format('F j, Y');

            // In-app notification
            $notificationService->notifyProfile(
                profileId: $profile->profile_id,
                title: 'Time Deposit Maturity Reminder',
                description: "Your time deposit of {$formattedAmount} is maturing on {$formattedDate}. "
                    . "Please log in to set your maturity option (Re-Time Deposit or Transfer to Regular Savings) "
                    . "before the maturity date. If no action is taken, it will be automatically transferred to Regular Savings.",
            );

            // SMS notification
            if ($profile->mobile_number) {
                $smsMessage = "SLEM Coop: Your time deposit of {$formattedAmount} matures on {$formattedDate}. "
                    . "Log in to set your maturity option before it matures. "
                    . "Default: Auto-transfer to Regular Savings.";

                $notificationService->sendSms($profile->mobile_number, $smsMessage);
            }

            $this->info("Notified profile_id={$profile->profile_id} for transaction_id={$transaction->id}");
            $count++;
        }

        $this->info("Done. {$count} reminder(s) sent.");
    }
}