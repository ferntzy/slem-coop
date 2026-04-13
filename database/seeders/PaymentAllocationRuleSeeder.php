<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentAllocationSetting;
use App\Models\PaymentAllocationRule;

class PaymentAllocationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $setting = PaymentAllocationSetting::firstOrCreate(
            ['id' => 1],
            [
                'allow_partial'        => true,
                'allow_advance'        => true,
                'allow_overpayment'    => true,
                'auto_apply'           => true,
                'allow_void'           => true,
                'require_void_reason'  => true,
                'allow_edit'           => true,
                'require_edit_reason'  => true,
            ]
        );

        // Define the rules with bucket and label
        $rules = [
            ['component' => 'penalty',   'priority' => 1, 'bucket' => 'default', 'label' => 'Penalty'],
            ['component' => 'interest',  'priority' => 2, 'bucket' => 'default', 'label' => 'Interest'],
            ['component' => 'principal', 'priority' => 3, 'bucket' => 'default', 'label' => 'Principal'],
        ];

        foreach ($rules as $rule) {
            PaymentAllocationRule::updateOrCreate(
                [
                    'payment_allocation_setting_id' => $setting->id,
                    'component' => $rule['component'],
                ],
                [
                    'priority' => $rule['priority'],
                    'bucket'   => $rule['bucket'],
                    'label'    => $rule['label'], // ✅ provide label
                ]
            );
        }
    }
}
