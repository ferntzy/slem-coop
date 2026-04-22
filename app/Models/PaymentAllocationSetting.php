<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentAllocationSetting extends Model
{
    protected $table = 'payment_allocation_settings';

    protected $fillable = [
        'allow_partial',
        'allow_advance',
        'allow_overpayment',
        'auto_apply',
        'allow_void',
        'require_void_reason',
        'allow_edit',
        'require_edit_reason',
    ];

    protected $casts = [
        'allow_partial' => 'boolean',
        'allow_advance' => 'boolean',
        'allow_overpayment' => 'boolean',
        'auto_apply' => 'boolean',
        'allow_void' => 'boolean',
        'require_void_reason' => 'boolean',
        'allow_edit' => 'boolean',
        'require_edit_reason' => 'boolean',
    ];

    public function allocationRules(): HasMany
    {
        return $this->hasMany(PaymentAllocationRule::class, 'payment_allocation_setting_id')
            ->orderBy('priority');
    }

    // Always returns the single settings record, creates it if missing
    public static function getSingleton(): self
    {
        return static::with('allocationRules')->firstOrCreate([], [
            'allow_partial' => true,
            'allow_advance' => true,
            'allow_overpayment' => true,
            'auto_apply' => false,
            'allow_void' => true,
            'require_void_reason' => true,
            'allow_edit' => true,
            'require_edit_reason' => true,
        ]);
    }
}
