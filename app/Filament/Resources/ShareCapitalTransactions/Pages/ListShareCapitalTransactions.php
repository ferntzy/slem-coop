<?php

namespace App\Filament\Resources\ShareCapitalTransactions\Pages;

use App\Filament\Resources\ShareCapitalTransactions\ShareCapitalTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShareCapitalTransactions extends ListRecords
{
    protected static string $resource = ShareCapitalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
