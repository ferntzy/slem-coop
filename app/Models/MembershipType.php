<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MembershipType extends Model
{
    protected $primaryKey = 'membership_type_id';

    protected $fillable = [
        'name',
        'description',
        'fee',
    ];

    public function memberDetails()
    {
        return $this->hasMany(MemberDetail::class, 'membership_type_id', 'membership_type_id');
    }
}
