<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocationRule extends Model
{
    protected $table = 'payment_allocation_rules';

    protected $fillable = [
        'payment_allocation_setting_id',
        'component',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function setting(): BelongsTo
    {
        return $this->belongsTo(PaymentAllocationSetting::class, 'payment_allocation_setting_id');
    }
}
