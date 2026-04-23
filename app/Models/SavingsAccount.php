<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SavingsAccount extends Model
{
    protected $fillable = [
        'profile_id',
        'savings_type_id',
        'terms',
        'amount',
        'proof_of_payment',
        'status',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'profile_id');
    }

    public function savingsType(): BelongsTo
    {
        return $this->belongsTo(SavingsType::class, 'savings_type_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsAccountTransaction::class);
    }

    public function balance(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $transactions = SavingsAccountTransaction::query()
                    ->where('profile_id', $this->profile_id)
                    ->where('savings_type_id', $this->savings_type_id)
                    ->get();

                if ($transactions->isEmpty()) {
                    return round((float) ($this->amount ?? 0), 2);
                }

                return round($transactions->sum(function (SavingsAccountTransaction $transaction): float {
                    return $this->transactionDepositAmount($transaction) - $this->transactionWithdrawalAmount($transaction);
                }), 2);
            }
        );
    }

    protected function transactionDepositAmount(SavingsAccountTransaction $transaction): float
    {
        $deposit = (float) ($transaction->deposit ?? 0);

        if ($deposit > 0) {
            return $deposit;
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['deposit', 'credit'], true) || in_array($direction, ['credit', 'inflow'], true)) {
            return (float) ($transaction->amount ?? 0);
        }

        return 0.0;
    }

    protected function transactionWithdrawalAmount(SavingsAccountTransaction $transaction): float
    {
        $withdrawal = (float) ($transaction->withdrawal ?? 0);

        if ($withdrawal > 0) {
            return $withdrawal;
        }

        $type = Str::lower((string) ($transaction->type ?? ''));
        $direction = Str::lower((string) ($transaction->direction ?? ''));

        if (in_array($type, ['withdrawal', 'debit'], true) || in_array($direction, ['debit', 'outflow'], true)) {
            return (float) ($transaction->amount ?? 0);
        }

        return 0.0;
    }
}
