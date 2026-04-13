<?php

namespace App\Filament\Resources\SavingsAccounts\Pages;

use App\Filament\Resources\SavingsAccounts\SavingsAccountResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewSavingsAccount extends ViewRecord
{
    protected static string $resource = SavingsAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
