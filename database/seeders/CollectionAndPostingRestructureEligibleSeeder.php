<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\MemberDetail;
use App\Models\LoanApplication;
use App\Models\LoanAccount;
use App\Models\CollectionAndPosting;
use Carbon\Carbon;

class CollectionAndPostingRestructureEligibleSeeder extends Seeder
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
            ->where('purpose', 'Restructure Eligibility Test Loan')
            ->first();

        if (! $loanApplication) {
            $this->command?->warn('Restructure eligibility test loan application not found.');
            return;
        }

        $loanAccount = LoanAccount::where('loan_application_id', $loanApplication->loan_application_id)->first();

        if (! $loanAccount) {
            $this->command?->warn('Restructure eligibility test loan account not found.');
            return;
        }

        $loanNumber = 'LA-' . $loanAccount->loan_account_id;
        $memberName = trim($profile->first_name . ' ' . $profile->last_name);

        $releaseDate = Carbon::parse($loanAccount->release_date);

        $payments = [
            [
                'reference_number' => 'SEED-RESTRUCTURE-001',
                'amount_paid' => 2200.00,
                'payment_date' => $releaseDate->copy()->addMonth()->addDays(1)->toDateString(),
                'notes' => 'Seeded payment 1 for restructuring eligibility test.',
            ],
            [
                'reference_number' => 'SEED-RESTRUCTURE-002',
                'amount_paid' => 2200.00,
                'payment_date' => $releaseDate->copy()->addMonths(2)->addDays(1)->toDateString(),
                'notes' => 'Seeded payment 2 for restructuring eligibility test.',
            ],
            [
                'reference_number' => 'SEED-RESTRUCTURE-003',
                'amount_paid' => 2200.00,
                'payment_date' => $releaseDate->copy()->addMonths(3)->addDays(1)->toDateString(),
                'notes' => 'Seeded payment 3 for restructuring eligibility test.',
            ],
            [
                'reference_number' => 'SEED-RESTRUCTURE-004',
                'amount_paid' => 1200.00,
                'payment_date' => $releaseDate->copy()->addMonths(4)->addDays(1)->toDateString(),
                'notes' => 'Seeded payment 4 for restructuring eligibility test.',
            ],
        ];

        foreach ($payments as $payment) {
            CollectionAndPosting::updateOrCreate(
                [
                    'reference_number' => $payment['reference_number'],
                ],
                [
                    'loan_account_id' => $loanAccount->loan_account_id,
                    'loan_number' => $loanNumber,
                    'member_name' => $memberName,
                    'amount_paid' => $payment['amount_paid'],
                    'payment_date' => $payment['payment_date'],
                    'payment_method' => 'Cash',
                    'notes' => $payment['notes'],
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
                        'source' => 'CollectionAndPostingRestructureEligibleSeeder',
                    ]),
                ]
            );
        }

        $this->command?->info("Restructure-eligible posted payments created for {$loanNumber}");
    }
}