<?php

namespace App\Filament\Resources\LoanApplications\Pages;

use App\Filament\Resources\LoanApplications\LoanApplicationsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoanApplications extends ListRecords
{
    protected static string $resource = LoanApplicationsResource::class;

    protected string $view = 'filament.resources.loan-applications.pages.list-loan-applications';

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check();
    }

    protected function getHeaderActions(): array
    {
        $user = auth()->user();

        if ($user?->isMember()) {
            return [];
        }

        return [
            CreateAction::make(),
        ];
    }
}
