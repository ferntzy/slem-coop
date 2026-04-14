<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Models\Profile;
use App\Models\StaffDetail;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected ?int $staffBranchId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['staff_branch_id'] = $this->record->staffDetail?->branch_id;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->staffBranchId = $data['staff_branch_id'] ?? null;

        unset($data['staff_branch_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncStaffBranch($this->record);
    }

    protected function syncStaffBranch(Profile $profile): void
    {
        $branchScopedRoleNames = [
            'Manager',
            'Staff',
            'Cashier',
            'Account Officer',
            'Loan Officer',
            'Teller',
        ];

        if (in_array($profile->role?->name, $branchScopedRoleNames, true)) {
            if ($this->staffBranchId) {
                StaffDetail::updateOrCreate(
                    ['profile_id' => $profile->profile_id],
                    [
                        'position' => $profile->role?->name,
                        'branch_id' => $this->staffBranchId,
                    ],
                );
            }

            return;
        }

        $profile->staffDetail?->delete();
    }
}
