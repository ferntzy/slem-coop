<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;

class CollectionAndPostingLoanAccountBackfillSeeder extends Seeder
{
    /**
     * Backfill loan_account_id from loan_number values like LA-3
     */
    public function run(): void
    {
        $updated = 0;
        $skippedInvalid = 0;
        $skippedMissingLoanAccount = 0;

        CollectionAndPosting::query()
            ->whereNull('loan_account_id')
            ->whereNotNull('loan_number')
            ->where('loan_number', 'like', 'LA-%')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$updated, &$skippedInvalid, &$skippedMissingLoanAccount) {
                foreach ($rows as $row) {
                    $loanNumber = trim((string) $row->loan_number);

                    // Accept only formats like LA-3, LA-25, LA-100
                    if (!preg_match('/^LA-(\d+)$/', $loanNumber, $matches)) {
                        $skippedInvalid++;

                        $this->command?->warn("Skipped invalid loan_number [{$loanNumber}] on collection ID {$row->id}");
                        continue;
                    }

                    $parsedLoanAccountId = (int) $matches[1];

                    if ($parsedLoanAccountId <= 0) {
                        $skippedInvalid++;

                        $this->command?->warn("Skipped non-positive parsed loan_account_id from [{$loanNumber}] on collection ID {$row->id}");
                        continue;
                    }

                    $loanAccountExists = LoanAccount::query()
                        ->where('loan_account_id', $parsedLoanAccountId)
                        ->exists();

                    if (! $loanAccountExists) {
                        $skippedMissingLoanAccount++;

                        $this->command?->warn("Skipped missing loan_account_id {$parsedLoanAccountId} from [{$loanNumber}] on collection ID {$row->id}");
                        continue;
                    }

                    $row->update([
                        'loan_account_id' => $parsedLoanAccountId,
                    ]);

                    $updated++;

                    $this->command?->info("Updated collection ID {$row->id}: {$loanNumber} -> loan_account_id {$parsedLoanAccountId}");
                }
            });

        $this->command?->info('----------------------------------------');
        $this->command?->info("Backfill completed.");
        $this->command?->info("Updated: {$updated}");
        $this->command?->info("Skipped invalid loan_number: {$skippedInvalid}");
        $this->command?->info("Skipped missing loan_account: {$skippedMissingLoanAccount}");
        $this->command?->info('----------------------------------------');
    }
}