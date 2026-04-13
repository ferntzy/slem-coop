<?php

namespace App\Filament\Resources\PaymentAllocationLogics\Pages;

use App\Filament\Resources\PaymentAllocationLogics\PaymentAllocationLogicResource;
use App\Models\PaymentAllocationSetting;
use Filament\Resources\Pages\EditRecord;

class ManagePaymentAllocationLogic extends EditRecord
{
    protected static string $resource = PaymentAllocationLogicResource::class;

    // No custom $view — let EditRecord use its default view

    public function mount(int | string $record = null): void
    {
        $singleton = PaymentAllocationSetting::getSingleton();
        parent::mount($singleton->getKey());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Payment Allocation Settings saved.';
    }

    protected function hasCancelAction(): bool
{
    return false;
}
}