<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenaltyRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'frequency',
        'value_type',
        'value',
        'grace_period_days',
        'max_penalty_cap',
        'is_escalating',
        'escalation_interval',
        'escalation_increment',
        'escalation_max_value',
        'status',
        'is_default',
    ];

    protected $casts = [
        'is_escalating'        => 'boolean',
        'value'                => 'decimal:2',
        'max_penalty_cap'      => 'decimal:2',
        'escalation_increment' => 'decimal:2',
        'escalation_max_value' => 'decimal:2',
        'is_default'           => 'boolean',
    ];
    protected static function booted(): void
    {
        static::saving(function ($rule) {
            if ($rule->is_default) {
                static::where('id', '!=', $rule->id)->update([
                    'is_default' => false,
                ]);
            }
        });
    }

    /**
     * Calculate the effective penalty rate for a given number of overdue days.
     */
    public function effectiveRate(int $overdueDays): float
    {
        $rate = (float) $this->value;

        if (!$this->is_escalating || !$this->escalation_interval || !$this->escalation_increment) {
            return $rate;
        }

        // How many escalation intervals have passed?
        $intervals = (int) floor($overdueDays / $this->escalation_interval);
        $rate += $intervals * (float) $this->escalation_increment;

        // Cap the rate if a ceiling is set
        if ($this->escalation_max_value) {
            $rate = min($rate, (float) $this->escalation_max_value);
        }

        return $rate;
    }

    /**
     * Calculate the total penalty for a given outstanding amount and overdue days.
     */
    public function calculate(float $outstandingAmount, int $overdueDays): float
    {
        // Respect grace period
        if ($overdueDays <= $this->grace_period_days) {
            return 0.0;
        }

        $effectiveOverdueDays = $overdueDays - $this->grace_period_days;
        $rate = $this->effectiveRate($effectiveOverdueDays);

        // Determine number of periods (daily or monthly)
        $periods = $this->frequency === 'daily'
            ? $effectiveOverdueDays
            : (int) floor($effectiveOverdueDays / 30);

        if ($periods <= 0) {
            return 0.0;
        }

        // Calculate total penalty
        $penalty = $this->value_type === 'percentage'
            ? ($outstandingAmount * ($rate / 100)) * $periods
            : $rate * $periods;

        // Apply cap if set
        if ($this->max_penalty_cap !== null) {
            $penalty = min($penalty, (float) $this->max_penalty_cap);
        }

        return round($penalty, 2);
    }
}
