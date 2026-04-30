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
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }
}
