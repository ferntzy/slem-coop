<?php

namespace App\Filament\Resources\MemberDetails\Pages;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMemberDetail extends ViewRecord
{
    protected static string $resource = MemberDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
