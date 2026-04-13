<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
public function run(): void
{
    $this->call([
        RolesAndPermissionsSeeder::class,
        BranchSeeder::class,
        MembershipTypeSeeder::class,
        LoanTypeSeeder::class,
        SavingsTypeSeeder::class,
        CoopFeeTypeSeeder::class,

        SystemSettingsSeeder::class,
        CoopIdSeeder::class,

        UserSeeder::class,
        MemberDetailSeeder::class,
        MemberSpouseSeeder::class,
        MemberCoMakerSeeder::class,

        CoopFeeSeeder::class,

        PaymentAllocationSettingSeeder::class,
        PaymentAllocationRuleSeeder::class,
        PaymentAllocationConfigSeeder::class,
        PenaltyRuleSeeder::class,

        LoanApplicationPenaltyTestSeeder::class,
        CollectionAndPostingPenaltyTestSeeder::class,

        LoanApplicationRestructureEligibleSeeder::class,
        CollectionAndPostingRestructureEligibleSeeder::class,

        NewsEventsSeeder::class,
    ]);
    }
}