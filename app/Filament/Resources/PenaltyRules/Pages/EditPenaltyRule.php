<?php

namespace App\Filament\Resources\PenaltyRules\Pages;

use App\Filament\Resources\PenaltyRules\PenaltyRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPenaltyRule extends EditRecord
{
    protected static string $resource = PenaltyRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
