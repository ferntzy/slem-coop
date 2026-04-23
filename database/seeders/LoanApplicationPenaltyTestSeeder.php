<?php

namespace Database\Seeders;

use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanApplicationCashflow;
use App\Models\LoanType;
use App\Models\MemberDetail;
use App\Models\PenaltyRule;
use App\Models\Profile;
use App\Services\LoanAmortizationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LoanApplicationPenaltyTestSeeder extends Seeder
{
    public function run(): void
    {
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

        $loanType = LoanType::where('name', 'Guaranteed Loan')->first();

        if (! $loanType) {
            $this->command?->warn('Loan type not found: Guaranteed Loan');

            return;
        }

        $penaltyRule = PenaltyRule::where('status', 'active')->first();

        if (! $penaltyRule) {
            $this->command?->warn('No active penalty rule found.');

            return;
        }

        $releaseDate = Carbon::now()->subMonths(3)->startOfMonth();
        $termMonths = 6;
        $principalAmount = 12000.00;
        $interestRate = 2.00; // monthly percent

        $schedule = app(LoanAmortizationService::class)->generate(
            loanAmount: $principalAmount,
            monthlyInterestRatePercent: $interestRate,
            termMonths: $termMonths,
            releaseDate: $releaseDate,
        );

        $monthlyAmortization = $schedule[0]['amortization'] ?? 0;

        $loanApplication = LoanApplication::updateOrCreate(
            [
                'member_id' => $memberDetail->id,
                'purpose' => 'Penalty Seeder Test Loan',
            ],
            [
                'loan_type_id' => $loanType->loan_type_id,
                'application_type' => 'New',
                'parent_loan_account_id' => null,
                'amount_requested' => $principalAmount,
                'term_months' => $termMonths,
                'penalty_rule_id' => $penaltyRule->id,
                'status' => 'Approved',
                'release_date' => $releaseDate->toDateString(),
                'maturity_date' => $releaseDate->copy()->addMonths($termMonths)->toDateString(),
                'evaluation_notes' => 'Seeded approved test loan for penalty verification.',
                'bici_notes' => 'System-generated seed data for overdue payment testing.',
                'submitted_at' => $releaseDate->copy()->subDays(5),
                'approved_at' => $releaseDate->copy()->subDays(2),
                'collateral_document' => null,
                'collateral_status' => 'Approved',
                'collateral_type' => 'land_title',
                'cashflow_documents' => [],
            ]
        );

        LoanApplicationCashflow::updateOrCreate(
            [
                'loan_application_id' => $loanApplication->loan_application_id,
                'row_type' => 'income',
                'label' => 'Salary / Wages',
            ],
            [
                'category' => 'salary',
                'amount' => 28000.00,
                'notes' => 'Regular monthly salary',
            ]
        );

        LoanApplicationCashflow::updateOrCreate(
            [
                'loan_application_id' => $loanApplication->loan_application_id,
                'row_type' => 'expense',
                'label' => 'Living Expenses',
            ],
            [
                'category' => 'living_expenses',
                'amount' => 9000.00,
                'notes' => 'Estimated living expenses',
            ]
        );

        $loanAccount = LoanAccount::updateOrCreate(
            [
                'loan_application_id' => $loanApplication->loan_application_id,
            ],
            [
                'profile_id' => $profile->profile_id,
                'penalty_rule_id' => $penaltyRule->id,
                'principal_amount' => $principalAmount,
                'interest_rate' => $interestRate,
                'term_months' => $termMonths,
                'release_date' => $releaseDate->toDateString(),
                'maturity_date' => $releaseDate->copy()->addMonths($termMonths)->toDateString(),
                'monthly_amortization' => $monthlyAmortization,
                'balance' => $principalAmount,
                'status' => 'Active',
            ]
        );

        $this->command?->info("Penalty test loan application seeded. Loan Application ID: {$loanApplication->loan_application_id}");
        $this->command?->info("Penalty test loan account seeded. Loan Account ID: {$loanAccount->loan_account_id}");
    }
}
