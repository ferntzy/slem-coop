<?php

namespace Database\Seeders;

use App\Models\CoopFee;
use App\Models\CoopFeeType;
use Illuminate\Database\Seeder;

class CoopFeeSeeder extends Seeder
{
    public function run(): void
    {
        $loanApplication = CoopFeeType::where('key', 'loan_application')->first();
        $restructure = CoopFeeType::where('key', 'restructure')->first();
        $reloan = CoopFeeType::where('key', 'reloan')->first();

        if (! $loanApplication || ! $restructure || ! $reloan) {
            $this->command?->warn('Coop fee types not found. Run CoopFeeTypeSeeder first.');

            return;
        }

        $fees = [
            [
                'coop_fee_type_id' => $loanApplication->id,
                'type' => 'shared_capital',
                'name' => 'Shared Capital',
                'amount' => null,
                'percentage' => 2.00,
                'is_percentage' => true,
                'description' => 'Shared capital contribution deducted as a percentage of the loan amount.',
                'status' => 'active',
                'group' => 'Coop Fees',
            ],
            [
                'coop_fee_type_id' => $loanApplication->id,
                'type' => 'insurance',
                'name' => 'Insurance',
                'amount' => 150.00,
                'percentage' => null,
                'is_percentage' => false,
                'description' => 'Fixed insurance fee charged per loan application.',
                'status' => 'active',
                'group' => 'Coop Fees',
            ],
            [
                'coop_fee_type_id' => $loanApplication->id,
                'type' => 'processing_fee',
                'name' => 'Processing Fee',
                'amount' => 200.00,
                'percentage' => null,
                'is_percentage' => false,
                'description' => 'Fixed processing fee charged for loan evaluation and release.',
                'status' => 'active',
                'group' => 'Coop Fees',
            ],
            [
                'coop_fee_type_id' => $restructure->id,
                'type' => 'processing_fee',
                'name' => 'Processing Fee',
                'amount' => 100.00,
                'percentage' => null,
                'is_percentage' => false,
                'description' => 'Fixed processing fee charged for restructuring.',
                'status' => 'active',
                'group' => 'Coop Fees',
            ],
            [
                'coop_fee_type_id' => $reloan->id,
                'type' => 'processing_fee',
                'name' => 'Processing Fee',
                'amount' => 100.00,
                'percentage' => null,
                'is_percentage' => false,
                'description' => 'Fixed processing fee charged for reloan.',
                'status' => 'active',
                'group' => 'Coop Fees',
            ],
        ];

        foreach ($fees as $fee) {
            CoopFee::updateOrCreate(
                [
                    'coop_fee_type_id' => $fee['coop_fee_type_id'],
                    'type' => $fee['type'],
                ],
                $fee
            );
        }
    }
}
