<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'minimum_initial_deposit',
        'maintaining_balance',
        'minimum_terms',
        'interest_rate',
        'deposit_allowed',
        'withdrawal_allowed',
        'is_active',
    ];

    protected $casts = [
        'minimum_initial_deposit' => 'decimal:2',
        'maintaining_balance' => 'decimal:2',
        'minimum_terms' => 'integer',
        'interest_rate' => 'decimal:2',
        'deposit_allowed' => 'boolean',
        'withdrawal_allowed' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function savingsAccountTransaction(): HasMany
    {
        return $this->hasMany(SavingsAccountTransaction::class);
    }
}
