<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberCoMaker extends Model
{
     protected $fillable = [
        'member_detail_id',
        'full_name',
        'relationship',
        'contact_number',
        'address',
        'occupation',
        'employer_name',
        'monthly_income',
    ];

    protected $casts = [
        'monthly_income' => 'decimal:2',
    ];

    public function member()
    {
        return $this->belongsTo(MemberDetail::class, 'member_detail_id');
    }
}
