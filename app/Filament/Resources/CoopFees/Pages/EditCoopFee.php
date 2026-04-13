<?php

namespace App\Filament\Resources\CoopFees\Pages;

use App\Filament\Resources\CoopFees\CoopFeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoopFee extends EditRecord
{
    protected static string $resource = CoopFeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
