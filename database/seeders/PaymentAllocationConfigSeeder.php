<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PaymentAllocationConfig;

class PaymentAllocationConfigSeeder extends Seeder
{
    public function run(): void
    {

    PaymentAllocationConfig::truncate();
        $configs = [
            [
                'name' => 'Penalty',
                'column_name' => 'penalty',
                'sort_order' => 1,
            ],
            [
                'name' => 'Interest',
                'column_name' => 'interest',
                'sort_order' => 2,
            ],
            [
                'name' => 'Principal',
                'column_name' => 'principal',
                'sort_order' => 3,
            ],

        ];

        foreach ($configs as $config) {
            DB::table('payment_allocation_configs')->updateOrInsert(
                ['column_name' => $config['column_name']],
                array_merge($config, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
