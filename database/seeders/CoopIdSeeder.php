<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoopIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = now()->year;           // e.g. 2025, 2026, ...
        $prefix = "COOP-{$year}-";

        // Find the highest number already used in the current year
        $lastCoopId = DB::table('users')
            ->where('coop_id', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING_INDEX(coop_id, '-', -1) AS UNSIGNED) DESC")
            ->value('coop_id');

        $nextNumber = 1;

        if ($lastCoopId) {
            $lastNumber = (int) substr(strrchr($lastCoopId, '-'), 1);
            $nextNumber = $lastNumber + 1;
        }

        // Get users without coop_id (usually the ones just created by UserSeeder)
        $usersWithoutCoop = User::query()
            ->whereNull('coop_id')
            ->orWhere('coop_id', '')
            ->orderBy('user_id')
            ->get();

        if ($usersWithoutCoop->isEmpty()) {
            $this->command->info('No users without coop_id found.');
            return;
        }

        $this->command->info("Assigning coop_ids starting from {$prefix}" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT));

        foreach ($usersWithoutCoop as $user) {
            $padded = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $coopId = $prefix . $padded;

            // Very simple collision check (should almost never happen in seeder)
            while (User::where('coop_id', $coopId)->exists()) {
                $nextNumber++;
                $padded = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                $coopId = $prefix . $padded;
            }

            $user->coop_id = $coopId;
            $user->saveQuietly();

            $this->command->info("  Assigned → {$user->username} : {$coopId}");

            $nextNumber++;
        }

        $this->command->info('Coop ID assignment completed.');
    }
}
