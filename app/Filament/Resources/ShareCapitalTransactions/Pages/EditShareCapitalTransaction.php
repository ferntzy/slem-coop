<?php

namespace App\Filament\Resources\ShareCapitalTransactions\Pages;

use App\Filament\Resources\ShareCapitalTransactions\ShareCapitalTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShareCapitalTransaction extends EditRecord
{
    protected static string $resource = ShareCapitalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
