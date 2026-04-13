<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\MemberDetail;
use App\Models\MemberSpouse;

class MemberSpouseSeeder extends Seeder
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

        MemberSpouse::updateOrCreate(
            [
                'member_detail_id' => $memberDetail->id,
            ],
            [
                'full_name' => 'Maria Member',
                'birthdate' => '1996-08-20',
                'occupation' => 'Teacher',
                'employer_name' => 'Baybay National High School',
                'employer_address' => 'Baybay City, Leyte',
                'source_of_income' => 'Employment',
                'tin' => '200-000-000-001',
            ]
        );

        $this->command?->info("Spouse seeded for member_detail_id {$memberDetail->id}");
    }
}