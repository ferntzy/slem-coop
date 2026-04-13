<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoopFeeType;

class CoopFeeTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Loan Application',
                'key' => 'loan_application',
                'description' => 'Fees applied to new loan applications.',
                'status' => 'active',
            ],
            [
                'name' => 'Loan Restructure',
                'key' => 'restructure',
                'description' => 'Fees applied to loan restructures.',
                'status' => 'active',
            ],
            [
                'name' => 'Reloan',
                'key' => 'reloan',
                'description' => 'Fees applied to reloan processing.',
                'status' => 'active',
            ],
        ];

        foreach ($types as $type) {
            CoopFeeType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }
    }
}