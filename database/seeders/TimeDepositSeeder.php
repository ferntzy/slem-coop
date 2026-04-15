<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SavingsAccountTransaction;
use Carbon\Carbon;

class TimeDepositSeeder extends Seeder
{
    public function run(): void
    {

        $profileId = 6;


        $timeDepositTypeId = 1;


        SavingsAccountTransaction::create([
            'profile_id' => $profileId,
            'savings_type_id' => $timeDepositTypeId,
            'type' => 'Deposit',
            'deposit' => 50000,
            'terms' => 12,
            'status' => 'ongoing',
            'transaction_date' => Carbon::now()->subMonths(2), // 2 months pa
            'notes' => 'Test - Not yet matured',
        ]);


        SavingsAccountTransaction::create([
            'profile_id' => $profileId,
            'savings_type_id' => $timeDepositTypeId,
            'type' => 'Deposit',
            'deposit' => 80000,
            'terms' => 4,
            'status' => 'completed',
            'transaction_date' => Carbon::now()->subMonths(6), // lagpas na
            'notes' => 'Test - Matured',
        ]);
    }
}
