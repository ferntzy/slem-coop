<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanAccount extends Model
{
    protected $primaryKey = 'loan_account_id';

    protected $fillable = [
        'loan_application_id',
        'profile_id',
        'principal_amount',
        'interest_rate',
        'term_months',
        'release_date',
        'maturity_date',
        'monthly_amortization',
        'balance',
        'status',
        'parent_loan_account_id',
        'restructure_application_id',
        'restructured_at',
        'shared_capital_fee',
        'insurance_fee',
        'processing_fee',
        'coop_fee_total',
        'net_release_amount',
        'penalty_rule_id',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'monthly_amortization' => 'decimal:2',
        'balance' => 'decimal:2',
        'release_date' => 'date',
        'maturity_date' => 'date',
        'restructured_at' => 'date',
        'shared_capital_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'coop_fee_total' => 'decimal:2',
        'net_release_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
    ];

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id', 'loan_application_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function collectionsAndPostings(): HasMany
    {
        return $this->hasMany(CollectionAndPosting::class, 'loan_account_id', 'loan_account_id');
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'loan_account_id', 'loan_account_id');
    }

    public function parentLoanAccount(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_loan_account_id', 'loan_account_id');
    }

    public function childRestructuredLoans(): HasMany
    {
        return $this->hasMany(self::class, 'parent_loan_account_id', 'loan_account_id');
    }

    public function restructureApplication(): BelongsTo
    {
        return $this->belongsTo(RestructureApplication::class, 'restructure_application_id', 'restructure_application_id');
    }

    public function penaltyRule(): BelongsTo
    {
        return $this->belongsTo(PenaltyRule::class, 'penalty_rule_id', 'id');
    }
}
