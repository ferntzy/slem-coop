<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\MemberDetail;
use App\Models\MembershipType;
use App\Models\Profile;
use App\Models\Role;
use Illuminate\Database\Seeder;

class MemberDetailSeeder extends Seeder
{
    public function run(): void
    {
        $memberRole = Role::where('name', 'Member')->firstOrFail();

        $hilongos = Branch::where('code', '001')->firstOrFail();
        $baybay = Branch::where('code', '002')->firstOrFail();

        $associateType = MembershipType::where('name', 'Associate Member')->firstOrFail();
        $regularType = MembershipType::where('name', 'Regular Member')->firstOrFail();

        $memberProfile = Profile::updateOrCreate(
            ['email' => 'member@example.com'],
            [
                'first_name' => 'Member',
                'middle_name' => null,
                'last_name' => 'User',
                'mobile_number' => '09123456780',
                'birthdate' => '1998-02-10',
                'sex' => 'Male',
                'address' => 'Hilongos, Leyte',
                'city' => 'Hilongos',
                'zip_code' => '6524',
                'roles_id' => $memberRole->id,
                'civil_status' => 'Single',
                'tin' => '100-000-000-003',
            ]
        );

        MemberDetail::updateOrCreate(
            ['profile_id' => $memberProfile->profile_id],
            [
                'share_capital_balance' => 5000.00,
                'regular_at' => null,
                'member_no' => 'MEM-0001',
                'occupation' => 'Factory Worker',
                'employer_name' => 'ABC Manufacturing',
                'monthly_income_range' => '10,000 - 20,000',
                'id_type' => 'PhilID',
                'id_number' => 'PH-123456789',
                'emergency_full_name' => 'Raul User',
                'emergency_phone' => '09123456789',
                'emergency_relationship' => 'Father',
                'signature_path' => null,
                'employment_info' => 'Employed full-time',
                'monthly_income' => 18000.00,
                'membership_type_id' => $associateType->membership_type_id,
                'branch_id' => $hilongos->branch_id,
                'status' => 'Active',
                'years_in_coop' => 1,
                'dependents_count' => 0,
                'children_in_school_count' => 0,
            ]
        );

        $regularProfile = Profile::updateOrCreate(
            ['email' => 'regularmember@example.com'],
            [
                'first_name' => 'Regular',
                'middle_name' => null,
                'last_name' => 'Member',
                'mobile_number' => '09199887766',
                'birthdate' => '1995-06-15',
                'sex' => 'Male',
                'address' => 'Baybay City, Leyte',
                'city' => 'Baybay',
                'zip_code' => '6521',
                'roles_id' => $memberRole->id,
                'civil_status' => 'Married',
                'tin' => '100-000-000-006',
            ]
        );

        MemberDetail::updateOrCreate(
            ['profile_id' => $regularProfile->profile_id],
            [
                'share_capital_balance' => 25000.00,
                'regular_at' => now(),
                'member_no' => 'MEM-0002',
                'occupation' => 'Teacher',
                'employer_name' => 'Baybay National High School',
                'monthly_income_range' => '20,001 - 30,000',
                'id_type' => 'Driver License',
                'id_number' => 'D-987654321',
                'emergency_full_name' => 'Maria Member',
                'emergency_phone' => '09191112222',
                'emergency_relationship' => 'Spouse',
                'signature_path' => null,
                'employment_info' => 'Permanent employee',
                'monthly_income' => 28000.00,
                'membership_type_id' => $regularType->membership_type_id,
                'branch_id' => $baybay->branch_id,
                'status' => 'Active',
                'years_in_coop' => 5,
                'dependents_count' => 2,
                'children_in_school_count' => 1,
            ]
        );
    }
}
