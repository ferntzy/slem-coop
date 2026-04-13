<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AccessToken;
use Illuminate\Support\Str;

class AccessTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tokens = Str::random(60);

        AccessToken::create([
            'token' => $tokens,
            'origin' => 'http://coop-management.test',
        ]);
    }
}
