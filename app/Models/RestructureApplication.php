<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestructureApplication extends Model
{
    protected $table = 'restructure_applications';

    protected $primaryKey = 'restructure_application_id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'loan_application_id',
        'old_loan_account_id',
        'status',
        'new_principal',
        'new_interest',
        'new_maturity_date',
        'term_months',
        'remarks',
        'shared_capital_fee',
        'insurance_fee',
        'processing_fee',
        'coop_fee_total',
        'net_release_amount',
    ];

    protected $casts = [
        'new_principal' => 'decimal:2',
        'new_interest' => 'decimal:2',
        'shared_capital_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'coop_fee_total' => 'decimal:2',
        'net_release_amount' => 'decimal:2',
    ];

    public function loanApplication()
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id', 'loan_application_id');
    }

    public function loanPayments()
    {
        return $this->hasMany(
            LoanPayment::class,
            'loan_application_id',
            'loan_application_id'
        );
    }

    public function oldLoanAccount()
    {
        return $this->belongsTo(LoanAccount::class, 'old_loan_account_id', 'loan_account_id');
    }

    public function totalPaid()
    {
        return $this->loanPayments()->sum('principal_paid');
    }

    public function getTotalPaidAttribute()
    {
        return $this->loanPayments()->sum('principal_paid');
    }
}
