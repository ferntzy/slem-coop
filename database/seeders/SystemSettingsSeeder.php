<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'app_name'         => config('app.name', 'COOP'),
            'logo'             => null,
            'favicon'          => null,
            'primary_color'    => '#0d9488',
            'font'             => 'Rajdhani',
            'topbar_font_size' => '14',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value'      => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}











