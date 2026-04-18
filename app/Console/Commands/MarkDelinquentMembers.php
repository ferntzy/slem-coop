<?php

namespace App\Console\Commands;

use App\Models\CoopSetting;
use App\Models\LoanPayment;
use App\Models\MemberDetail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkDelinquentMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-delinquent-members';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically mark members as delinquent based on missed loan payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if auto marking is enabled
        $autoMarkEnabled = (bool) CoopSetting::get('member_status.auto_mark_delinquent', true);
        if (!$autoMarkEnabled) {
            $this->info('Auto marking delinquent members is disabled. Skipping...');
            return;
        }

        // Get the delinquent threshold months
        $thresholdMonths = (int) CoopSetting::get('member_status.delinquent_months_threshold', 3);

        $this->info("Marking members as delinquent after {$thresholdMonths} months of missed payments...");

        // Calculate the cutoff date (threshold months ago)
        $cutoffDate = Carbon::now()->subMonths($thresholdMonths);

        // Find members who have overdue payments beyond the threshold
        $delinquentProfileIds = LoanPayment::where('due_date', '<=', $cutoffDate)
            ->where('status', '!=', 'paid')
            ->whereHas('loanAccount', function ($query) {
                $query->where('status', 'active'); // Only consider active loans
            })
            ->with('loanAccount.profile')
            ->get()
            ->pluck('loanAccount.profile.profile_id')
            ->unique()
            ->toArray();

        if (empty($delinquentProfileIds)) {
            $this->info('No members found to mark as delinquent.');
            return;
        }

        // Update member status to delinquent
        $updatedCount = MemberDetail::whereIn('profile_id', $delinquentProfileIds)
            ->where('status', '!=', 'Delinquent') // Only update if not already delinquent
            ->update(['status' => 'Delinquent']);

        $this->info("Marked {$updatedCount} member(s) as delinquent.");
    }
}
