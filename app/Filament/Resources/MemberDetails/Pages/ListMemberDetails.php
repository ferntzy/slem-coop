<?php

namespace App\Filament\Resources\MemberDetails\Pages;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberDetails extends ListRecords
{
    protected static string $resource = MemberDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
