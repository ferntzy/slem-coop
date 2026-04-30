<?php

namespace App\Filament\Resources\MemberDetails\Pages;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberDetail extends EditRecord
{
    protected static string $resource = MemberDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Keep the existing branch_id if not changed
        $resolvedBranchId = ! empty($data['branch_id'])
            ? (int) $data['branch_id']
            : (int) ($this->record->branch_id ?? 0);

        $data['branch_id'] = $resolvedBranchId ?: 1; // Fallback to branch_id 1 if needed

        return $data;
    }
}
