<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDetail extends Model
{
    protected $table = 'staff_details';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'profile_id',
        'position',
        'staff_detailscol',
        'branch_id',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'branch_id');
    }
}
