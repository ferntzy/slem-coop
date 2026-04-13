<?php

namespace App\Console\Commands;

use App\Models\LoanPayment;
use App\Services\NotificationService;
use App\Support\CoopSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPaymentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-payment-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send payment due reminders, overdue notices, and confirmations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->sendDueReminders();
        $this->sendOverdueNotices();
    }

    private function sendDueReminders(): void
    {
        $this->info('Sending due reminders...');

        $daysBeforeDue = $this->getDueReminderDays();

        foreach ($daysBeforeDue as $days) {
            $date = Carbon::today()->addDays($days);
            $payments = LoanPayment::where('due_date', $date)
                ->where('status', '!=', 'paid')
                ->whereNull('due_reminder_sent_at')
                ->with('loanAccount.profile')
                ->get();

            foreach ($payments as $payment) {
                $profile = $payment->loanAccount->profile;
                if (! $profile) {
                    continue;
                }

                app(NotificationService::class)->notifyDueDateReminder(
                    $profile->profile_id,
                    (float) $payment->amount_due,
                    $payment->due_date->format('M d, Y'),
                    $days,
                );

                $payment->update(['due_reminder_sent_at' => now()]);
            }
        }
    }

    private function sendOverdueNotices(): void
    {
        $this->info('Sending overdue notices...');

        $overdueLevels = $this->getOverdueNoticeDays();

        foreach ($overdueLevels as $days) {
            $overdueDate = Carbon::today()->subDays($days);

            $payments = LoanPayment::where('due_date', $overdueDate)
                ->where('status', '!=', 'paid')
                ->where(function ($query) use ($days) {
                    $query->whereNull('overdue_notice_sent_at')
                        ->orWhere(function ($q) use ($days) {
                            $q->where('overdue_notice_level', '!=', $days)
                                ->where('overdue_notice_sent_at', '<', now()->subHours(23));
                        });
                })
                ->with('loanAccount.profile')
                ->get();

            foreach ($payments as $payment) {
                $profile = $payment->loanAccount->profile;
                if (! $profile) {
                    continue;
                }

                app(NotificationService::class)->notifyOverdueNotice(
                    $profile->profile_id,
                    (float) $payment->amount_due,
                    $payment->due_date->format('M d, Y'),
                    $days,
                );

                $payment->update([
                    'overdue_notice_level' => $days,
                    'overdue_notice_sent_at' => now(),
                ]);
            }
        }
    }

    private function getDueReminderDays(): array
    {
        $raw = CoopSettings::get('payment.due_reminder_days', '3,0');

        return array_values(array_filter(array_map(
            fn ($value) => is_numeric($value) ? (int) trim($value) : null,
            explode(',', (string) $raw)
        )));
    }

    private function getOverdueNoticeDays(): array
    {
        $raw = CoopSettings::get('payment.overdue_notice_days', '1,7,30');

        return array_values(array_filter(array_map(
            fn ($value) => is_numeric($value) ? (int) trim($value) : null,
            explode(',', (string) $raw)
        )));
    }
}
