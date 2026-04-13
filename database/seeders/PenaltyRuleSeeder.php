<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenaltyRule;

class PenaltyRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'name'                 => 'Standard Daily Penalty',
                'description'          => 'Charges 0.5% of the outstanding balance per day after a 5-day grace period. Rate escalates by 0.1% every 30 days, capped at 2%.',
                'frequency'            => 'daily',
                'value_type'           => 'percentage',
                'value'                => 0.50,
                'grace_period_days'    => 5,
                'max_penalty_cap'      => null,
                'is_escalating'        => true,
                'escalation_interval'  => 30,
                'escalation_increment' => 0.10,
                'escalation_max_value' => 2.00,
                'status'               => 'active',
            ],
            [
                'name'                 => 'Monthly Fixed Penalty',
                'description'          => 'Charges a fixed ₱500 per month overdue. Escalates by ₱100 every month, capped at ₱1,000.',
                'frequency'            => 'monthly',
                'value_type'           => 'fixed',
                'value'                => 500.00,
                'grace_period_days'    => 0,
                'max_penalty_cap'      => 5000.00,
                'is_escalating'        => true,
                'escalation_interval'  => 30,
                'escalation_increment' => 100.00,
                'escalation_max_value' => 1000.00,
                'status'               => 'active',
            ],
        ];

        foreach ($rules as $rule) {
            PenaltyRule::updateOrCreate(
                ['name' => $rule['name']],
                $rule
            );
        }
    }
}
