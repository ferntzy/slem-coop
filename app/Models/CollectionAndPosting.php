<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionAndPosting extends Model
{
    protected $fillable = [
        'loan_account_id',
        'loan_number',
        'member_name',
        'amount_paid',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'file_path',
        'original_file_name',
        'mime_type',
        'file_size',
        'document_type',
        'status',
        'posted_by_user_id',
        'audit_trail',
        'confirmation_notification_sent_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
        'scheduled_due_date' => 'date',
        'audit_trail' => 'array',
        'notification_sent_at' => 'datetime',
        'overdue_notice_level' => 'integer',
        'confirmation_notification_sent_at' => 'datetime',
    ];

    public function postedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id', 'user_id');
    }

    public function loanAccount(): BelongsTo
    {
        return $this->belongsTo(LoanAccount::class, 'loan_account_id', 'loan_account_id');
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'loan_account_id', 'loan_account_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(PaymentNotification::class, 'collection_posting_id');
    }
}
