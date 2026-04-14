<?php

namespace App\Filament\Resources\MemberDetails\Pages;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberDetail extends CreateRecord
{
    protected static string $resource = MemberDetailResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['branch_id']) && auth()->user()?->branchId()) {
            $data['branch_id'] = auth()->user()->branchId();
        }

        return $data;
    }
}
