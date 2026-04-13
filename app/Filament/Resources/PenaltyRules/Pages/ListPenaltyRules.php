<?php

namespace App\Filament\Resources\PenaltyRules\Pages;

use App\Filament\Resources\PenaltyRules\PenaltyRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPenaltyRules extends ListRecords
{
    protected static string $resource = PenaltyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
