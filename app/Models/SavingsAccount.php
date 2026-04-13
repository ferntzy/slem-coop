<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsAccount extends Model
{
    protected $fillable = [
        'profile_id',
        'savings_type_id',
        'terms',
        'amount',
        'proof_of_payment',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function savingsType(): BelongsTo
    {
        return $this->belongsTo(SavingsType::class, 'savings_type_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsAccountTransaction::class);
    }
}
