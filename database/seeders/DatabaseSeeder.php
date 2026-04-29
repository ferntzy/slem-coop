<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AccessTokenSeeder::class,
            BranchSeeder::class,

            MembershipTypeSeeder::class,
            RolesAndPermissionsSeeder::class,

            UserSeeder::class,
            MemberDetailSeeder::class,
            MemberSpouseSeeder::class,
            MemberCoMakerSeeder::class,

            CoopFeeSeeder::class,
            CoopFeeTypeSeeder::class,
            CoopIdSeeder::class,

            CollectionAndPostingLoanAccountBackfillSeeder::class,
            CollectionAndPostingPenaltyTestSeeder::class,
            CollectionAndPostingRestructureEligibleSeeder::class,

            LoanApplicationPenaltyTestSeeder::class,
            LoanApplicationRestructureEligibleSeeder::class,
            LoanPaymentPenaltyTestSeeder::class,

            LoanTypeSeeder::class,

            NewsEventsSeeder::class,
            OrientationSettingsSeeder::class,

            PaymentAllocationConfigSeeder::class,
            PaymentAllocationRuleSeeder::class,
            PaymentAllocationSettingSeeder::class,

            PenaltyRuleSeeder::class,

            SavingsTypeSeeder::class,
            SystemSettingsSeeder::class,
            TimeDepositSeeder::class,

            UserQrCodeSeeder::class,

            DormantSavingsDemoSeeder::class,
        ]);

    }
}
