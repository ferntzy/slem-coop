<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\CoopSetting;
use App\Models\MemberDetail;
use App\Models\MembershipType;
use App\Models\Profile;
use App\Models\SavingsAccount;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DormantSavingsDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $memberRoleId = Role::query()
            ->where('name', UserRole::Member->value)
            ->value('id')
            ?? Role::query()->value('id');

        if (! $memberRoleId) {
            $this->command?->warn('No role records found. Seed roles before running DormantSavingsDemoSeeder.');

            return;
        }

        $dormantProfile = Profile::query()->updateOrCreate(
            ['email' => 'dormant.demo.member@slem-coop.test'],
            [
                'first_name' => 'Dormant',
                'middle_name' => null,
                'last_name' => 'Demo',
                'mobile_number' => '09170000001',
                'birthdate' => '1990-01-01',
                'sex' => 'Male',
                'address' => 'Dormancy Demo Address',
                'roles_id' => $memberRoleId,
            ]
        );

        $activeProfile = Profile::query()->updateOrCreate(
            ['email' => 'active.demo.member@slem-coop.test'],
            [
                'first_name' => 'Active',
                'middle_name' => null,
                'last_name' => 'Demo',
                'mobile_number' => '09170000002',
                'birthdate' => '1992-01-01',
                'sex' => 'Female',
                'address' => 'Dormancy Demo Address',
                'roles_id' => $memberRoleId,
            ]
        );

        $membershipTypeId = MembershipType::query()->value('membership_type_id');
        $branchId = Branch::query()->where('is_active', true)->value('branch_id')
            ?? Branch::query()->value('branch_id');

        if (! $membershipTypeId || ! $branchId) {
            $this->command?->warn('Membership type or branch is missing. Seed membership types and branches before running DormantSavingsDemoSeeder.');

            return;
        }

        MemberDetail::query()->updateOrCreate(
            ['profile_id' => $dormantProfile->profile_id],
            [
                'membership_type_id' => $membershipTypeId,
                'branch_id' => $branchId,
                'status' => 'Dormant',
                'member_no' => 'DEMO-DORM-0001',
            ]
        );

        MemberDetail::query()->updateOrCreate(
            ['profile_id' => $activeProfile->profile_id],
            [
                'membership_type_id' => $membershipTypeId,
                'branch_id' => $branchId,
                'status' => 'Active',
                'member_no' => 'DEMO-ACTV-0001',
            ]
        );

        $demoSavingsType = SavingsType::query()->updateOrCreate(
            ['code' => 'SA DEMO DORMANCY'],
            [
                'name' => 'Dormancy Demo Savings',
                'description' => 'Demo savings type for dormant account verification.',
                'minimum_initial_deposit' => 1000,
                'maintaining_balance' => 500,
                'minimum_terms' => 2,
                'interest_rate' => 1.20,
                'deposit_allowed' => true,
                'withdrawal_allowed' => true,
                'is_active' => true,
            ]
        );

        CoopSetting::set('savings.dormancy_months_threshold', 24);
        CoopSetting::set('savings.auto_apply_dormancy_fee', true, 'boolean');
        CoopSetting::set('savings.dormancy_fee_amount', 30.00);
        CoopSetting::set('savings.dormancy_fee_near_zero_threshold', 1.00);
        CoopSetting::set('savings.apply_interest_on_dormant', true, 'boolean');

        $demoSavingsTypeId = (string) $demoSavingsType->id;

        // Reset demo members to a clean savings history so dormancy status is deterministic.
        SavingsAccount::query()
            ->whereIn('profile_id', [$dormantProfile->profile_id, $activeProfile->profile_id])
            ->delete();

        SavingsAccountTransaction::query()
            ->whereIn('profile_id', [$dormantProfile->profile_id, $activeProfile->profile_id])
            ->delete();

        // Create SavingsAccount records for the demo profiles
        $dormantSavingsAccount = SavingsAccount::query()->updateOrCreate(
            [
                'profile_id' => $dormantProfile->profile_id,
                'savings_type_id' => $demoSavingsTypeId,
            ],
            [
                'status' => 'Approved',
                'approved_at' => now(),
                'terms' => 1,
            ]
        );

        $activeSavingsAccount = SavingsAccount::query()->updateOrCreate(
            [
                'profile_id' => $activeProfile->profile_id,
                'terms' => 1,
                'savings_type_id' => $demoSavingsTypeId,
            ],
            [
                'status' => 'Approved',
                'approved_at' => now(),
            ]
        );

        SavingsAccountTransaction::query()->create([
            'profile_id' => $dormantProfile->profile_id,
            'savings_type_id' => $demoSavingsTypeId,
            'type' => 'Deposit',
            'direction' => 'deposit',
            'deposit' => 10000,
            'amount' => 10000,
            'status' => 'completed',
            'transaction_date' => now()->subMonths(30)->toDateString(),
            'created_at' => now()->subMonths(30),
            'updated_at' => now()->subMonths(30),
            'reference_no' => 'DEMO-DORMANT-INITIAL',
            'notes' => 'Dormancy demo: customer deposit from 30 months ago.',
            'posted_by_user_id' => null,
        ]);

        SavingsAccountTransaction::query()->create([
            'profile_id' => $activeProfile->profile_id,
            'savings_type_id' => $demoSavingsTypeId,
            'type' => 'Deposit',
            'direction' => 'deposit',
            'deposit' => 2500,
            'amount' => 2500,
            'status' => 'completed',
            'transaction_date' => now()->subMonths(1)->toDateString(),
            'created_at' => now()->subMonths(1),
            'updated_at' => now()->subMonths(1),
            'reference_no' => 'DEMO-ACTIVE-RECENT',
            'notes' => 'Dormancy demo: recent customer activity within threshold.',
            'posted_by_user_id' => null,
        ]);

        $this->command?->info('Dormancy demo data seeded successfully.');
        $this->command?->line('Dormant demo profile: dormant.demo.member@slem-coop.test');
        $this->command?->line('Active demo profile: active.demo.member@slem-coop.test');
        $this->command?->line('Savings type code: SA DEMO DORMANCY');
        $this->command?->line('Member detail rows seeded for both demo profiles.');
    }
}
