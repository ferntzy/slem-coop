<?php

namespace App\Filament\Resources\SavingsAccounts\Pages;

use App\Filament\Resources\SavingsAccounts\SavingsAccountResource;
use App\Models\SavingsType;
use Filament\Resources\Pages\CreateRecord;

class CreateSavingsAccount extends CreateRecord
{
    protected static string $resource = SavingsAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['account_number'])) {
            $data['account_number'] = 'SAV-' . now()->format('YmdHis');
        }

        $type = isset($data['savings_type_id'])
            ? SavingsType::find($data['savings_type_id'])
            : null;

        if ($type && empty($data['savings_type'])) {
            $data['savings_type'] = $type->code
                ? "{$type->name} ({$type->code})"
                : $type->name;
        }

        if ($type && empty($data['terms'])) {
            $data['terms'] = (int) ($type->minimum_terms ?? 0);
        }

        $data['balance'] = $data['balance'] ?? $data['amount'] ?? 0;

        return $data;
    }
}
