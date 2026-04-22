<?php

namespace Database\Seeders;

use App\Models\MemberCoMaker;
use App\Models\MemberDetail;
use App\Models\Profile;
use Illuminate\Database\Seeder;

class MemberCoMakerSeeder extends Seeder
{
    public function run(): void
    {
        $profile = Profile::where('email', 'regularmember@example.com')->first();

        if (! $profile) {
            $this->command?->warn('Profile not found for regularmember@example.com');

            return;
        }

        $memberDetail = MemberDetail::where('profile_id', $profile->profile_id)->first();

        if (! $memberDetail) {
            $this->command?->warn("MemberDetail not found for profile_id {$profile->profile_id}");

            return;
        }

        MemberCoMaker::updateOrCreate(
            [
                'member_detail_id' => $memberDetail->id,
                'full_name' => 'Jose Cruz',
            ],
            [
                'relationship' => 'Friend',
                'contact_number' => '09191234567',
                'address' => 'Baybay City, Leyte',
                'occupation' => 'Store Supervisor',
                'employer_name' => 'ABC Mart Baybay',
                'monthly_income' => 22000.00,
            ]
        );

        $this->command?->info("Co-maker seeded for member_detail_id {$memberDetail->id}");
    }
}
