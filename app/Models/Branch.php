<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branches'; // important if not default

    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_no',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
