<?php

namespace App\Filament\Resources\ShareCapitalTransactions\Pages;

use App\Filament\Resources\ShareCapitalTransactions\ShareCapitalTransactionResource;
use App\Models\MemberDetail;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateShareCapitalTransaction extends CreateRecord
{
    protected static string $resource = ShareCapitalTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['posted_by_user_id'] = $data['posted_by_user_id'] ?? auth()->id();

        $data['direction'] = match ($data['type']) {
            'deposit', 'adjustment_credit' => 'credit',
            'withdraw', 'adjustment_debit' => 'debit',
            default => 'credit',
        };

        // Optional safety: prevent withdraw/adjustment_debit beyond balance
        if ($data['direction'] === 'debit') {
            $balance = (float) (MemberDetail::query()
                ->where('profile_id', $data['profile_id'])
                ->value('share_capital_balance') ?? 0);

            if ((float) $data['amount'] > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Withdrawal amount cannot exceed the member’s current share capital balance.',
                ]);
            }
        }

        return $data;
    }
}