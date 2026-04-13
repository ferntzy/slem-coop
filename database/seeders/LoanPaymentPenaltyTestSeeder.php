<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use App\Models\MemberDetail;
use App\Models\LoanApplication;
use App\Models\LoanAccount;
use App\Models\LoanPayment;
use App\Services\LoanAmortizationService;
use Carbon\Carbon;

class LoanPaymentPenaltyTestSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('username', 'admin')->first();
        $profile = Profile::where('email', 'regularmember@example.com')->first();

        if (! $adminUser || ! $profile) {
            $this->command?->warn('Required user/profile not found.');
            return;
        }

        $memberDetail = MemberDetail::where('profile_id', $profile->profile_id)->first();

        if (! $memberDetail) {
            $this->command?->warn('Member detail not found.');
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

        $schedule = app(LoanAmortizationService::class)->generate(
            loanAmount: (float) $loanAccount->principal_amount,
            monthlyInterestRatePercent: (float) $loanAccount->interest_rate,
            termMonths: (int) $loanAccount->term_months,
            releaseDate: $loanAccount->release_date,
        );

        $firstRow = $schedule[0] ?? null;

        if (! $firstRow) {
            $this->command?->warn('No amortization schedule generated.');
            return;
        }

        $dueDate = Carbon::parse($firstRow['due_date']);
        $paymentDate = $dueDate->copy()->addDays(15);

        LoanPayment::updateOrCreate(
            [
                'loan_application_id' => $loanApplication->loan_application_id,
                'payment_date' => $paymentDate->toDateString(),
                'remarks' => 'Seeded late payment for penalty testing',
            ],
            [
                'loan_account_id' => $loanAccount->loan_account_id,
                'due_date' => $dueDate->toDateString(),
                'amount_paid' => 1000.00,
                'amount_due' => $firstRow['amortization'],
                'carry_forward' => 0.00,
                'payment_type' => 'Partial',
                'status' => 'Posted',
                'principal_paid' => 700.00,
                'interest_paid' => 200.00,
                'penalty_paid' => 100.00,
                'remaining_balance' => 11300.00,
                'posted_by' => $adminUser->user_id,
            ]
        );

        $this->command?->info('LoanPayments penalty test row seeded.');
    }
}