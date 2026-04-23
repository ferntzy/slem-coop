<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplicationCollstat extends Model
{
    protected $table = 'loan_application_collstats';

    public $timestamps = false;

    protected $fillable = [
        'loan_application_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'reason',
        'changed_at',
    ];
}
