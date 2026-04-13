<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $table = 'loan_payments';

    protected $primaryKey = 'loan_payment_id';

    protected $fillable = [
        'loan_account_id',
        'loan_application_id',
        'payment_date',
        'due_date',
        'amount_paid',
        'amount_due',
        'carry_forward',
        'payment_type',
        'principal_paid',
        'interest_paid',
        'penalty_paid',
        'remaining_balance',
        'status',
        'posted_by',
        'remarks',
        'due_reminder_sent_at',
        'overdue_notice_level',
        'overdue_notice_sent_at',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date' => 'date',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'carry_forward' => 'decimal:2',
        'principal_paid' => 'decimal:2',
        'interest_paid' => 'decimal:2',
        'penalty_paid' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'due_reminder_sent_at' => 'datetime',
        'overdue_notice_level' => 'integer',
        'overdue_notice_sent_at' => 'datetime',
    ];

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class, 'loan_application_id', 'loan_application_id');
    }

    public function loanAccount(): BelongsTo
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id', 'loan_account_id');
    }

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by', 'user_id');
    }

    public static function resolvePaymentType(float $amountPaid, float $amountDue): string
    {
        if ($amountPaid < $amountDue) {
            return 'Partial';
        } elseif ($amountPaid > $amountDue) {
            return 'Overpayment';
        }

        return 'Advance';
    }

    public static function resolveCarryForward(float $amountPaid, float $amountDue): float
    {
        $diff = $amountPaid - $amountDue;

        return $diff > 0 ? round($diff, 2) : 0.00;
    }
}
