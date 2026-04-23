<?php

namespace App\Filament\Resources\RestructureApplications\Pages;

use App\Filament\Resources\RestructureApplications\RestructureApplicationsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestructureApplications extends ListRecords
{
    protected static string $resource = RestructureApplicationsResource::class;

    protected string $view = 'filament.resources.restructure-applications.pages.list-restructure-applications';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
