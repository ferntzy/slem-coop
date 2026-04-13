<?php

namespace App\Filament\Resources\RestructureApplications\Pages;

use App\Filament\Resources\RestructureApplications\RestructureApplicationsResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRestructureApplications extends ViewRecord
{
    protected static string $resource = RestructureApplicationsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
