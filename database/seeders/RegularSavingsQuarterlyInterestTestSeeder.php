<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\SavingsAccountTransaction;
use App\Models\SavingsType;
use Illuminate\Database\Seeder;

class RegularSavingsQuarterlyInterestTestSeeder extends Seeder
{
    public function run(): void
    {
        $regularSavingsType = SavingsType::query()
            ->where('code', 'SA 02')
            ->orWhere('name', 'Regular Savings')
            ->first();

        if (! $regularSavingsType) {
            $this->command?->error('Regular Savings type not found. Run SavingsTypeSeeder first.');

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
            ->whereIn('notes', [
                'Seeder test - regular savings opening deposit',
                'Seeder test - regular savings withdrawal',
                'Quarterly ADB interest',
            ])
            ->delete();

        SavingsAccountTransaction::query()->create([
            'profile_id' => $profile->profile_id,
            'savings_type_id' => (string) $regularSavingsType->getKey(),
            'type' => 'Deposit',
            'deposit' => 10000,
            'status' => 'completed',
            'transaction_date' => '2026-01-01 09:00:00',
            'notes' => 'Seeder test - regular savings opening deposit',
        ]);

        SavingsAccountTransaction::query()->create([
            'profile_id' => $profile->profile_id,
            'savings_type_id' => (string) $regularSavingsType->getKey(),
            'type' => 'Withdrawal',
            'withdrawal' => 5000,
            'status' => 'completed',
            'transaction_date' => '2026-02-01 09:00:00',
            'notes' => 'Seeder test - regular savings withdrawal',
        ]);

        $this->command?->info('Regular savings quarterly interest test data seeded.');
        $this->command?->line('Target member: regularmember@example.com');
        $this->command?->line('This seeder does not change Coop Settings.');
        $this->command?->line('Run: php artisan savings:apply-quarterly-interest --quarter=2026-01');
    }
}
