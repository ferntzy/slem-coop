<?php

namespace App\Filament\Resources\CoopFees\Pages;

use App\Filament\Resources\CoopFees\CoopFeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoopFees extends ListRecords
{
    protected static string $resource = CoopFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
