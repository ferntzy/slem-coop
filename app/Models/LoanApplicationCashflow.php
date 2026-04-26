<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplicationCashflow extends Model
{
    protected $primaryKey = 'loan_application_cashflow_id';

    protected $fillable = [
        'loan_application_id',
        'label',
        'row_type',
        'category',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function application()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id', 'loan_application_id');
    }
}
