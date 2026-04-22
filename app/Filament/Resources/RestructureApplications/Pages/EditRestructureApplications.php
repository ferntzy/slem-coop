<?php

namespace App\Filament\Resources\RestructureApplications\Pages;

use App\Filament\Resources\RestructureApplications\RestructureApplicationsResource;
use App\Services\CoopFeeCalculatorService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRestructureApplications extends EditRecord
{
    protected static string $resource = RestructureApplicationsResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $principal = (float) ($data['new_principal'] ?? 0);

        $fees = app(CoopFeeCalculatorService::class)
            ->calculate('restructure', $principal);

        $data['shared_capital_fee'] = $fees['shared_capital_fee'] ?? 0;
        $data['insurance_fee'] = $fees['insurance_fee'] ?? 0;
        $data['processing_fee'] = $fees['processing_fee'] ?? 0;
        $data['coop_fee_total'] = $fees['coop_fee_total'] ?? 0;
        $data['net_release_amount'] = $fees['net_release_amount'] ?? 0;

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
