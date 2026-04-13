<?php

namespace App\Filament\Resources\PaymentAllocationLogics\Pages;

use App\Filament\Resources\PaymentAllocationLogics\PaymentAllocationLogicResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentAllocationLogic extends EditRecord
{
    protected static string $resource = PaymentAllocationLogicResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
