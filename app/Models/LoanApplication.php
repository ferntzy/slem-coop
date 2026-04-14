<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PenaltyRule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanApplication extends Model
{
    protected $primaryKey = 'loan_application_id';
    

    protected $fillable = [
        'collateral_status',
        'collateral_type',
        'collateral_document',
        'member_id',
        'loan_type_id',
        'amount_requested',
        'term_months',
        'purpose',
        'status',
        'evaluation_notes',
        'bici_notes',
        'submitted_at',
        'approved_at',
        'cashflow_documents',
        'shared_capital_fee',
        'insurance_fee',
        'processing_fee',
        'coop_fee_total',
        'net_release_amount',
        'penalty_rule_id',
    ];

    protected $casts = [
        'amount_requested' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'cashflow_documents' => 'array',
        'shared_capital_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'coop_fee_total' => 'decimal:2',
        'net_release_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($loanApplication) {
            if (! $loanApplication->penalty_rule_id) {
                $loanApplication->penalty_rule_id = PenaltyRule::where('is_default', true)->value('id');
            }
        });
    }
    public function penaltyRule(): BelongsTo
    {
        return $this->belongsTo(PenaltyRule::class, 'penalty_rule_id', 'id');
    }
    public function type()
    {
        return $this->belongsTo(LoanType::class, 'loan_type_id', 'loan_type_id');
    }

    public function loanAccount()
    {
        return $this->hasOne(LoanAccount::class, 'loan_application_id', 'loan_application_id');
    }

    public function payments()
    {
        return $this->hasMany(LoanPayment::class, 'loan_application_id', 'loan_application_id');
    }

    public function totalPaid()
    {
        return $this->payments()->sum('principal_paid');
    }

    public function member()
    {
        
        return $this->belongsTo(\App\Models\MemberDetail::class, 'member_id', 'id');
        
    }
    

    public function product()
    {
        return $this->belongsTo(LoanProduct::class, 'loan_product_id', 'loan_product_id');
    }

    public function documents()
    {
        return $this->hasMany(LoanApplicationDocument::class, 'loan_application_id', 'loan_application_id');
    }

    public function cashflows()
    {
        return $this->hasMany(LoanApplicationCashflow::class, 'loan_application_id', 'loan_application_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(\App\Models\LoanApplicationStatusLog::class, 'loan_application_id', 'loan_application_id')
            ->orderByDesc('changed_at');
    }

    public function getRouteKeyName(): string
    {
        return 'loan_application_id';
    }

    public function getCashflowAmount(string $category, string $rowType): float
    {
        return (float) ($this->cashflows()
            ->where('category', $category)
            ->where('row_type', $rowType)
            ->value('amount') ?? 0);
    }

    public function syncCashflowsFromForm(array $data): void
    {
        $this->cashflows()->delete();

        $rows = [
            ['label' => 'Salary / Wages', 'row_type' => 'income', 'category' => 'salary', 'amount' => $data['salary'] ?? 0],
            ['label' => 'Business Income', 'row_type' => 'income', 'category' => 'business_income', 'amount' => $data['business_income'] ?? 0],
            ['label' => 'Remittances', 'row_type' => 'income', 'category' => 'remittances', 'amount' => $data['remittances'] ?? 0],
            ['label' => 'Other Income', 'row_type' => 'income', 'category' => 'other_income', 'amount' => $data['other_income'] ?? 0],

            ['label' => 'Living Expenses', 'row_type' => 'expense', 'category' => 'living_expenses', 'amount' => $data['living_expenses'] ?? 0],
            ['label' => 'Business Expenses', 'row_type' => 'expense', 'category' => 'business_expenses', 'amount' => $data['business_expenses'] ?? 0],
            ['label' => 'Existing Loan Payments', 'row_type' => 'expense', 'category' => 'existing_loan_payments', 'amount' => $data['existing_loan_payments'] ?? 0],
            ['label' => 'Other Expenses', 'row_type' => 'expense', 'category' => 'other_expenses', 'amount' => $data['other_expenses'] ?? 0],
        ];

        foreach ($rows as $row) {
            if ((float) $row['amount'] > 0) {
                $this->cashflows()->create([
                    'label' => $row['label'],
                    'row_type' => $row['row_type'],
                    'category' => $row['category'],
                    'amount' => $row['amount'],
                    'notes' => null,
                ]);
            }
        }
    }
    public function previousLoan()
{
    return $this->belongsTo(\App\Models\LoanAccount::class, 'reloan_from_loan_account_id', 'loan_account_id');
}

public function loanType(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\LoanType::class);
}

}

