<?php

use App\Models\CoopSetting;
use App\Models\Profile;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use App\Services\MemberSavingsBalanceService;
use App\Services\SavingsDormancyService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

function createSavingsTestProfile(string $email = 'member@example.com'): Profile
{
    $roleId = DB::table('roles')->insertGetId([
        'name' => 'Member',
        'guard_name' => 'web',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Profile::query()->create([
        'first_name' => 'Regular',
        'middle_name' => null,
        'last_name' => 'Member',
        'email' => $email,
        'mobile_number' => '09123456789',
        'roles_id' => $roleId,
    ]);
}

function createSavingsTypes(): array
{
    $timeDepositType = SavingsType::query()->create([
        'name' => 'Time Deposit',
        'code' => 'SA 01',
        'description' => 'Deposit grows over time with fixed withdrawal date.',
        'minimum_initial_deposit' => 10000,
        'interest_rate' => 2,
        'is_active' => true,
        'minimum_terms' => 4,
    ]);

    $regularSavingsType = SavingsType::query()->create([
        'name' => 'Regular Savings',
        'code' => 'SA 02',
        'description' => 'Securing money savings that can be withdrawn anytime.',
        'minimum_initial_deposit' => 1000,
        'maintaining_balance' => 500,
        'interest_rate' => 1,
        'is_active' => true,
        'minimum_terms' => 2,
    ]);

    return [$timeDepositType, $regularSavingsType];
}

it('applies quarterly adb interest to regular savings once per quarter', function () {
    $profile = createSavingsTestProfile();
    [, $regularSavingsType] = createSavingsTypes();

    CoopSetting::set('savings.regular_interest_rate_percent', 3.00);

    SavingsAccountTransaction::query()->create([
        'profile_id' => $profile->profile_id,
        'savings_type_id' => (string) $regularSavingsType->getKey(),
        'type' => 'Deposit',
        'deposit' => 1000,
        'status' => 'completed',
        'transaction_date' => '2026-01-01 09:00:00',
        'notes' => 'Opening deposit',
    ]);

    SavingsAccountTransaction::query()->create([
        'profile_id' => $profile->profile_id,
        'savings_type_id' => (string) $regularSavingsType->getKey(),
        'type' => 'Withdrawal',
        'withdrawal' => 500,
        'status' => 'completed',
        'transaction_date' => '2026-02-01 09:00:00',
        'notes' => 'Withdrawal',
    ]);

    $this->artisan('savings:apply-quarterly-interest', ['--quarter' => '2026-01'])
        ->assertSuccessful();

    $interestTransaction = SavingsAccountTransaction::query()
        ->where('profile_id', $profile->profile_id)
        ->where('savings_type_id', (string) $regularSavingsType->getKey())
        ->where('type', 'Interest')
        ->first();

    expect($interestTransaction)->not->toBeNull();
    expect((float) $interestTransaction->deposit)->toBe(4.97);
    expect($interestTransaction->notes)->toBe('Quarterly ADB interest');

    $this->artisan('savings:apply-quarterly-interest', ['--quarter' => '2026-01'])
        ->assertSuccessful();

    expect(
        SavingsAccountTransaction::query()
            ->where('profile_id', $profile->profile_id)
            ->where('savings_type_id', (string) $regularSavingsType->getKey())
            ->where('type', 'Interest')
            ->count()
    )->toBe(1);
});

it('defaults matured time deposits to regular savings when no maturity option is selected', function () {
    $profile = createSavingsTestProfile('default-maturity@example.com');
    [$timeDepositType, $regularSavingsType] = createSavingsTypes();

    CoopSetting::set('savings.time_deposit_interest_rate_percent', 2.00);

    $deposit = SavingsAccountTransaction::query()->create([
        'profile_id' => $profile->profile_id,
        'savings_type_id' => (string) $timeDepositType->getKey(),
        'type' => 'Deposit',
        'deposit' => 10000,
        'terms' => 12,
        'status' => 'ongoing',
        'transaction_date' => now()->subMonths(12)->subDay(),
        'notes' => 'Matured deposit without selected option',
    ]);

    $this->artisan('process:timedeposits')->assertSuccessful();

    $deposit->refresh();

    $transfer = SavingsAccountTransaction::query()
        ->where('profile_id', $profile->profile_id)
        ->where('savings_type_id', (string) $regularSavingsType->getKey())
        ->where('notes', 'like', 'Transferred from matured time deposit #%')
        ->first();

    expect($deposit->status)->toBe('completed');
    expect($transfer)->not->toBeNull();
    expect((float) $transfer->deposit)->toBe(10200.00);
});

it('re-times matured deposits when the maturity option is renew time deposit', function () {
    $profile = createSavingsTestProfile('renew-maturity@example.com');
    [$timeDepositType] = createSavingsTypes();

    CoopSetting::set('savings.time_deposit_interest_rate_percent', 2.00);

    $deposit = SavingsAccountTransaction::query()->create([
        'profile_id' => $profile->profile_id,
        'savings_type_id' => (string) $timeDepositType->getKey(),
        'type' => 'Deposit',
        'deposit' => 12000,
        'terms' => 6,
        'status' => 'ongoing',
        'maturity_action' => 'renew_time_deposit',
        'maturity_action_selected_at' => now()->subDays(2),
        'transaction_date' => now()->subMonths(6)->subDay(),
        'notes' => 'Matured deposit with renew option',
    ]);

    $this->artisan('process:timedeposits')->assertSuccessful();

    $deposit->refresh();

    $renewedDeposit = SavingsAccountTransaction::query()
        ->where('profile_id', $profile->profile_id)
        ->where('savings_type_id', (string) $timeDepositType->getKey())
        ->where('id', '!=', $deposit->id)
        ->where('notes', 'like', 'Renewed from matured time deposit #%')
        ->first();

    expect($deposit->status)->toBe('completed');
    expect($renewedDeposit)->not->toBeNull();
    expect((float) $renewedDeposit->deposit)->toBe(12120.00);
    expect($renewedDeposit->status)->toBe('ongoing');
    expect($renewedDeposit->terms)->toBe(6);
});

it('charges dormancy fees monthly and returns to active after customer activity', function () {
    $profile = createSavingsTestProfile('dormancy-cycle@example.com');
    [, $regularSavingsType] = createSavingsTypes();
    $regularSavingsType->update(['interest_rate' => 0]);

    CoopSetting::set('savings.dormancy_months_threshold', 24);
    CoopSetting::set('savings.dormancy_fee_amount', 30.00);
    CoopSetting::set('savings.auto_apply_dormancy_fee', true, 'boolean');
    CoopSetting::set('savings.apply_interest_on_dormant', false, 'boolean');

    SavingsAccountTransaction::query()->create([
        'profile_id' => $profile->profile_id,
        'savings_type_id' => (string) $regularSavingsType->getKey(),
        'type' => 'Deposit',
        'direction' => 'deposit',
        'deposit' => 1000,
        'amount' => 1000,
        'status' => 'completed',
        'transaction_date' => '2024-01-15 09:00:00',
        'notes' => 'Opening deposit',
    ]);

    $service = app(SavingsDormancyService::class);

    $monthOne = $service->processMonthly(Carbon::parse('2026-04-15'));

    expect($monthOne['evaluated_accounts'])->toBe(1);
    expect($monthOne['dormant_accounts'])->toBe(1);
    expect($monthOne['dormancy_fees_posted'])->toBe(1);
    expect(app(MemberSavingsBalanceService::class)->getRegularSavingsBalance($profile->profile_id))->toBe(970.0);

    $monthTwo = $service->processMonthly(Carbon::parse('2026-05-15'));

    expect($monthTwo['dormant_accounts'])->toBe(1);
    expect($monthTwo['dormancy_fees_posted'])->toBe(1);
    expect(app(MemberSavingsBalanceService::class)->getRegularSavingsBalance($profile->profile_id))->toBe(940.0);

    SavingsAccountTransaction::query()->create([
        'profile_id' => $profile->profile_id,
        'savings_type_id' => (string) $regularSavingsType->getKey(),
        'type' => 'Deposit',
        'direction' => 'deposit',
        'deposit' => 500,
        'amount' => 500,
        'status' => 'completed',
        'transaction_date' => '2026-06-01 10:00:00',
        'notes' => 'Customer deposit after dormancy',
    ]);

    $reactivated = $service->processMonthly(Carbon::parse('2026-06-15'));

    expect($reactivated['dormant_accounts'])->toBe(0);
    expect($reactivated['dormancy_fees_posted'])->toBe(0);
    expect(app(MemberSavingsBalanceService::class)->getRegularSavingsBalance($profile->profile_id))->toBe(1440.0);
});
