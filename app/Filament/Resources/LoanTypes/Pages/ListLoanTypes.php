<?php

namespace App\Filament\Resources\LoanTypes\Pages;

use App\Filament\Resources\LoanTypes\LoanTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanTypes extends ListRecords
{
    protected static string $resource = LoanTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
