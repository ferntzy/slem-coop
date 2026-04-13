<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LoanType;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loanTypes = [
            [
                'name' => 'E-Cash Loan',
                'description' => 'No fixed loan limit. Maximum 3% interest. Maximum term: 2 years. Collateral required for large loan amounts.',
                'max_interest_rate' => 3.00,
                'max_term_months' => 24,
                'min_amount' => 1000.00,
                'max_amount' => null,
                'amount_calculation_type' => 'Manual',
                'amount_multiplier' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Guaranteed Loan',
                'description' => 'Based on fixed deposit or share capital. Maximum loanable amount is share capital × 2. Maximum 2% interest. Maximum term: 2 years.',
                'max_interest_rate' => 2.00,
                'max_term_months' => 24,
                'min_amount' => 1000.00,
                'max_amount' => null,
                'amount_calculation_type' => 'Multiplier',
                'amount_multiplier' => 2.00,
                'is_active' => true,
            ],
            [
                'name' => 'Instant/Emergency Loan',
                'description' => 'Maximum loan amount of 15,000 with maximum term of 3 months.',
                'max_interest_rate' => 3.00,
                'max_term_months' => 3,
                'min_amount' => 500.00,
                'max_amount' => 15000.00,
                'amount_calculation_type' => 'Fixed',
                'amount_multiplier' => null,
                'is_active' => true,
            ],
        ];

        foreach ($loanTypes as $loanType) {
            LoanType::updateOrCreate(
                ['name' => $loanType['name']],
                $loanType
            );
        }
    }
}
