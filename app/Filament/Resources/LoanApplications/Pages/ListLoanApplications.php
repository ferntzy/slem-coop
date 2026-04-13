<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanApplications extends ListRecords
{
    protected static string $resource = LoanApplicationsResource::class;

    protected string $view = 'filament.resources.loan-applications.pages.list-loan-applications';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
