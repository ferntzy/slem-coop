<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplicationStatusLog extends Model
{
    protected $table = 'loan_application_status_logs';

    protected $primaryKey = 'loan_application_status_log_id';

    public $timestamps = false; // because table uses changed_at, not created_at/updated_at

    protected $fillable = [
        'loan_application_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id', 'loan_application_id');
    }
}
