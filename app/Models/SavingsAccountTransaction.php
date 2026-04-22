<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsAccountTransaction extends Model
{
    protected $fillable = [
        'profile_id',
        'savings_type_id',
        'terms',
        'deposit',
        'withdrawal',
        'amount',
        'type',
        'direction',
        'status',
        'maturity_action',
        'maturity_action_selected_at',
        'transaction_date',
        'reference_no',
        'notes',
        'proof_of_transaction',
        'posted_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'maturity_action_selected_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function savingsType(): BelongsTo
    {
        return $this->belongsTo(SavingsType::class, 'savings_type_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id', 'user_id');
    }

    public function isMatured(): bool
    {
        if (! $this->transaction_date || ! $this->terms) {
            return false;
        }

        return $this->transaction_date->copy()->addMonths((int) $this->terms)->lte(now());
    }
}
