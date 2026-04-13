<?php

namespace App\Filament\Resources\MembershipTypes\Pages;

use App\Filament\Resources\MembershipTypes\MembershipTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembershipTypes extends ListRecords
{
    protected static string $resource = MembershipTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
