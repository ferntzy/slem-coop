<?php

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use App\Filament\Widgets\LoanHistoryTable;
use App\Models\LoanAccount;
use App\Models\LoanApplication;
use App\Models\LoanType;
use App\Models\MemberDetail;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function createAdminUser(): User
{
    $user = User::query()->create([
        'username' => 'admin-user-'.uniqid(),
        'password' => 'password',
        'is_active' => true,
        'must_change_password' => false,
    ]);

    $user->assignRole('Admin');

    return $user;
}

function createMemberWithLoan(): array
{
    $memberRoleId = DB::table('roles')
        ->whereIn('name', ['Member', 'member'])
        ->value('id');

    if (! $memberRoleId) {
        $memberRoleId = DB::table('roles')->insertGetId([
            'name' => 'Member',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $branchId = DB::table('branches')->insertGetId([
        'name' => 'Test Branch '.uniqid(),
        'code' => 'TB-'.fake()->unique()->numerify('###'),
        'address' => 'Test Address',
        'contact_no' => '09123456789',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $membershipTypeId = DB::table('membership_types')->insertGetId([
        'name' => 'Regular '.uniqid(),
        'description' => 'Test membership type',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $profile = Profile::query()->create([
        'first_name' => 'Loan',
        'middle_name' => null,
        'last_name' => 'Member',
        'email' => 'loan-member-'.uniqid().'@example.com',
        'mobile_number' => '09123456789',
        'roles_id' => $memberRoleId,
        'branch_id' => $branchId,
    ]);

    $member = MemberDetail::query()->create([
        'profile_id' => $profile->profile_id,
        'membership_type_id' => $membershipTypeId,
        'branch_id' => $branchId,
        'status' => 'Active',
    ]);

    $loanType = LoanType::query()->create([
        'name' => 'Emergency Loan',
        'description' => 'Short-term emergency loan',
        'max_interest_rate' => 2.5,
        'max_term_months' => 12,
        'max_amount' => 50000,
        'min_amount' => 1000,
        'amount_calculation_type' => 'fixed',
        'amount_multiplier' => null,
        'is_active' => true,
    ]);

    $loanApplication = LoanApplication::query()->create([
        'member_id' => $member->getKey(),
        'loan_type_id' => $loanType->loan_type_id,
        'amount_requested' => 12000,
        'term_months' => 12,
        'purpose' => 'Medical needs',
        'status' => 'Approved',
        'submitted_at' => now()->subDays(5),
        'approved_at' => now()->subDays(3),
    ]);

    LoanAccount::query()->create([
        'loan_application_id' => $loanApplication->loan_application_id,
        'profile_id' => $profile->profile_id,
        'principal_amount' => 12000,
        'interest_rate' => 2.5,
        'term_months' => 12,
        'release_date' => now()->subDays(3)->toDateString(),
        'maturity_date' => now()->addMonths(12)->toDateString(),
        'monthly_amortization' => 1100,
        'balance' => 10000,
        'status' => 'Active',
    ]);

    return [$member, $loanApplication];
}

it('shows member loan history inside the loan history tab widget', function () {
    $this->actingAs(createAdminUser());

    [$member, $loanApplication] = createMemberWithLoan();

    Livewire::test(LoanHistoryTable::class, ['record' => $member])
        ->assertCanSeeTableRecords([$loanApplication])
        ->assertTableActionExists('inspect', $loanApplication);
});

it('does not register the loan history relation manager on the member resource', function () {
    expect(MemberDetailResource::getRelations())->toBe([]);
});
