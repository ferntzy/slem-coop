<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyCollectionEntry extends Model
{
    protected $fillable = [
        'ao_user_id',
        'collection_date',
        'system_total',
        'transaction_count',
        'cash_on_hand',
        'variance',
        'status',
        'submitted_at',
        'verified_at',
        'verified_by_user_id',
        'notes',
    ];

    protected $casts = [
        'system_total' => 'decimal:2',
        'cash_on_hand' => 'decimal:2',
        'variance' => 'decimal:2',
        'collection_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function ao(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ao_user_id', 'user_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id', 'user_id');
    }
}
