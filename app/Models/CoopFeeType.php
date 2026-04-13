<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CoopFeeType extends Model
{
    protected $fillable = [
        'name',
        'key',
        'description',
        'status',
    ];

    public function fees(): HasMany
    {
        return $this->hasMany(CoopFee::class, 'coop_fee_type_id');
    }
}
