<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Illuminate\Database\Seeder;

class TimeDepositInterestTestSeeder extends Seeder
{
    public function run(): void
    {
        $timeDepositType = SavingsType::query()
            ->where('code', 'SA 01')
            ->orWhere('name', 'Time Deposit')
            ->first();

        $regularSavingsType = SavingsType::query()
            ->where('code', 'SA 02')
            ->orWhere('name', 'Regular Savings')
            ->first();

        if (! $timeDepositType || ! $regularSavingsType) {
            $this->command?->error('Required savings types not found. Run SavingsTypeSeeder first.');

            return;
        }

        $profile = Profile::query()
            ->where('email', 'regularmember@example.com')
            ->first();

        if (! $profile) {
            $this->command?->error('Profile not found for regularmember@example.com.');

            return;
        }

        SavingsAccountTransaction::query()
            ->where('profile_id', $profile->profile_id)
            ->where(function ($query): void {
                $query->whereIn('notes', [
                    'Seeder test - matured time deposit default transfer',
                    'Seeder test - matured time deposit renew option',
                    'Seeder test - not yet matured time deposit',
                ])->orWhere('notes', 'like', 'Transferred from matured time deposit #%')
                    ->orWhere('notes', 'like', 'Renewed from matured time deposit #%');
            })
            ->delete();

        SavingsAccountTransaction::query()->create([
            'profile_id' => $profile->profile_id,
            'savings_type_id' => (string) $timeDepositType->getKey(),
            'type' => 'Deposit',
            'deposit' => 10000,
            'terms' => 4,
            'status' => 'ongoing',
            'transaction_date' => now()->subMonths(4)->subDay(),
            'notes' => 'Seeder test - matured time deposit default transfer',
        ]);

        SavingsAccountTransaction::query()->create([
            'profile_id' => $profile->profile_id,
            'savings_type_id' => (string) $timeDepositType->getKey(),
            'type' => 'Deposit',
            'deposit' => 15000,
            'terms' => 6,
            'status' => 'ongoing',
            'maturity_action' => 'renew_time_deposit',
            'maturity_action_selected_at' => now()->subDays(2),
            'transaction_date' => now()->subMonths(6)->subDay(),
            'notes' => 'Seeder test - matured time deposit renew option',
        ]);

        SavingsAccountTransaction::query()->create([
            'profile_id' => $profile->profile_id,
            'savings_type_id' => (string) $timeDepositType->getKey(),
            'type' => 'Deposit',
            'deposit' => 20000,
            'terms' => 12,
            'status' => 'ongoing',
            'transaction_date' => now()->subMonths(2),
            'notes' => 'Seeder test - not yet matured time deposit',
        ]);

        $this->command?->info('Time deposit interest test data seeded.');
        $this->command?->line('Target member: regularmember@example.com');
        $this->command?->line('This seeder does not change Coop Settings.');
        $this->command?->line('Run: php artisan process:timedeposits');
    }
}
