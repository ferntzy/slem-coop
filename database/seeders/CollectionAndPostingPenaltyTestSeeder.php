<?php

namespace Database\Seeders;

use App\Models\CollectionAndPosting;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\MemberDetail;
use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CollectionAndPostingPenaltyTestSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('username', 'admin')->first();

        if (! $adminUser) {
            $this->command?->warn('Admin user not found.');

            return;
        }

        $profile = Profile::where('email', 'regularmember@example.com')->first();

        if (! $profile) {
            $this->command?->warn('Profile not found for regularmember@example.com');

            return;
        }

        $memberDetail = MemberDetail::where('profile_id', $profile->profile_id)->first();

        if (! $memberDetail) {
            $this->command?->warn("MemberDetail not found for profile_id {$profile->profile_id}");

            return;
        }

        $loanApplication = LoanApplication::where('member_id', $memberDetail->id)
            ->where('purpose', 'Penalty Seeder Test Loan')
            ->first();

        if (! $loanApplication) {
            $this->command?->warn('Penalty test loan application not found.');

            return;
        }

        $loanAccount = LoanAccount::where('loan_application_id', $loanApplication->loan_application_id)->first();

        if (! $loanAccount) {
            $this->command?->warn('Penalty test loan account not found.');

            return;
        }

        $loanNumber = 'LA-'.$loanAccount->loan_account_id;
        $memberName = trim($profile->first_name.' '.$profile->last_name);

        $releaseDate = Carbon::parse($loanAccount->release_date);

        $latePayment1Date = $releaseDate->copy()->addMonth()->addDays(15);
        $latePayment2Date = $releaseDate->copy()->addMonths(2)->addDays(20);

        CollectionAndPosting::updateOrCreate(
            [
                'reference_number' => 'SEED-PENALTY-001',
            ],
            [
                'loan_account_id' => $loanAccount->loan_account_id,
                'loan_number' => $loanNumber,
                'member_name' => $memberName,
                'amount_paid' => 1000.00,
                'payment_date' => $latePayment1Date->toDateString(),
                'payment_method' => 'Cash',
                'notes' => 'Seeded late partial payment for penalty testing.',
                'file_path' => null,
                'original_file_name' => null,
                'mime_type' => null,
                'file_size' => null,
                'document_type' => 'Official Receipt',
                'status' => 'Posted',
                'posted_by_user_id' => $adminUser->user_id,
                'audit_trail' => json_encode([
                    'action' => 'Posted',
                    'user_id' => $adminUser->user_id,
                    'timestamp' => now()->toISOString(),
                    'loan_id' => $loanApplication->loan_application_id,
                    'member_id' => $memberDetail->id,
                    'source' => 'CollectionAndPostingPenaltyTestSeeder',
                ]),
            ]
        );

        CollectionAndPosting::updateOrCreate(
            [
                'reference_number' => 'SEED-PENALTY-002',
            ],
            [
                'loan_account_id' => $loanAccount->loan_account_id,
                'loan_number' => $loanNumber,
                'member_name' => $memberName,
                'amount_paid' => 1500.00,
                'payment_date' => $latePayment2Date->toDateString(),
                'payment_method' => 'Cash',
                'notes' => 'Seeded second late payment for penalty testing.',
                'file_path' => null,
                'original_file_name' => null,
                'mime_type' => null,
                'file_size' => null,
                'document_type' => 'Official Receipt',
                'status' => 'Posted',
                'posted_by_user_id' => $adminUser->user_id,
                'audit_trail' => json_encode([
                    'action' => 'Posted',
                    'user_id' => $adminUser->user_id,
                    'timestamp' => now()->toISOString(),
                    'loan_id' => $loanApplication->loan_application_id,
                    'member_id' => $memberDetail->id,
                    'source' => 'CollectionAndPostingPenaltyTestSeeder',
                ]),
            ]
        );

        $this->command?->info("Late payment seed data created for {$loanNumber}");
    }
}
