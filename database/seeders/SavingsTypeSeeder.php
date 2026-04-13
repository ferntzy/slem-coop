<?php

namespace Database\Seeders;

use App\Models\SavingsType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SavingsTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $savingstypes = [
            [
                'name' => 'Time Deposit',
                'code' => 'SA 01',
                'description' => 'Deposit grows over time with fixed withdrawal date.',
                'minimum_initial_deposit' => 10000,
                'interest_rate' => 2,
                'is_active' => 1,
                'minimum_terms' => 4,
            ],
            [
                'name' => 'Regular Savings',
                'code' => 'SA 02',
                'description' => 'Securing money savings that can be withdrawn anytime.',
                'minimum_initial_deposit' => 1000,
                'maintaining_balance' => 500,
                'interest_rate' => 1,
                'is_active' => 1,
                'minimum_terms' => 2,

            ],
        ];
        foreach ($savingstypes as $type) {
            SavingsType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
