<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberSpouse extends Model
{
    protected $fillable = [
        'member_detail_id',
        'full_name',
        'birthdate',
        'occupation',
        'employer_name',
        'employer_address',
        'source_of_income',
        'tin',
        'monthly_income',
    ];

    public function member()
    {
        return $this->belongsTo(MemberDetail::class, 'member_detail_id');
    }
}
