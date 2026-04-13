<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShareCapitalTransaction extends Model
{
    protected $table = 'share_capital_transactions';

    protected $fillable = [
        'profile_id',
        'amount',
        'direction',        // credit | debit
        'type',             // deposit | withdraw | adjustment
        'transaction_date',
        'reference_no',
        'notes',
        'posted_by_user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id', 'user_id');
    }
}
