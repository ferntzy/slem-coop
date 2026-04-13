<?php

namespace App\Filament\Resources\MembershipApplications\Pages;

use App\Filament\Resources\MembershipApplications\MembershipApplicationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMembershipApplication extends ViewRecord
{
    protected static string $resource = MembershipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getMaxContentWidth(): ?string
    {
        return '7xl';
    }
}
