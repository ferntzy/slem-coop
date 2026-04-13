<?php

namespace Database\Seeders;


use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MembershipType;

class MembershipTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       MembershipType::updateOrCreate(
            ['membership_type_id' => 1],
            [
                'name' => 'Associate Member',
                'description' => 'Eligible to receive dividends and apply for loans. Loan amounts are generally smaller than regular members. No fixed loan threshold; approval depends on managers or loan officers.',
            ]
        );

        MembershipType::updateOrCreate(
            ['name' => 'Regular Member'],
            [
                'description' => 'Must meet required share capital to qualify. Eligible for dividends, time deposits, full cooperative services, and full loan offerings.',
            ]
        );
    }
}
