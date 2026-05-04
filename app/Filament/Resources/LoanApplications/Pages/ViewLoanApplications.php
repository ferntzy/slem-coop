<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLoanApplications extends ViewRecord
{
    protected static string $resource = LoanApplicationsResource::class;

    public function getTitle(): string
    {
        return 'View '.$this->record->member?->profile?->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
