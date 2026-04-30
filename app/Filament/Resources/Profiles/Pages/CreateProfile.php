<?php

namespace App\Filament\Resources\Profiles\Pages;

use App\Filament\Resources\Profiles\ProfileResource;
use App\Models\Profile;
use App\Models\Role;
use App\Models\StaffDetail;
use Filament\Resources\Pages\CreateRecord;

class CreateProfile extends CreateRecord
{
    protected static string $resource = ProfileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! $this->isBranchScopedRole((int) ($data['roles_id'] ?? 0))) {
            $data['branch_id'] = null;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncStaffDetail($this->record);
    }

    protected function syncStaffDetail(Profile $profile): void
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
            StaffDetail::updateOrCreate(
                ['profile_id' => $profile->profile_id],
                [
                    'position' => $profile->role?->name,
                ],
            );

            return;
        }

        $profile->staffDetail?->delete();
    }

    protected function isBranchScopedRole(int $roleId): bool
    {
        return Role::query()
            ->whereKey($roleId)
            ->whereIn('name', [
                'Manager',
                'Staff',
                'Cashier',
                'Account Officer',
                'Loan Officer',
                'Teller',
            ])
            ->exists();
    }
}
