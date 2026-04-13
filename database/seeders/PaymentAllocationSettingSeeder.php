<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentAllocationSetting;

class PaymentAllocationSettingSeeder extends Seeder
{
    public function run(): void
    {
        PaymentAllocationSetting::updateOrCreate(
            ['id' => 1],
            [
                'allow_partial' => true,
                'allow_advance' => true,
                'allow_overpayment' => true,
                'auto_apply' => true,
                'allow_void' => true,
                'require_void_reason' => true,
                'allow_edit' => true,
                'require_edit_reason' => true,
            ]
        );
    }
}