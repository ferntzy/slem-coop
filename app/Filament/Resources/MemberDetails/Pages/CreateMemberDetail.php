<?php

namespace App\Filament\Resources\MemberDetails\Pages;

use App\Filament\Resources\MemberDetails\MemberDetailResource;
use App\Models\Profile;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberDetail extends CreateRecord
{
    protected static string $resource = MemberDetailResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $resolvedBranchId = null;

        // Try to get branch from profile if it exists
        if (! empty($data['profile_id'])) {
            $resolvedBranchId = (int) (Profile::query()
                ->whereKey($data['profile_id'])
                ->value('branch_id') ?? 0);
        }

        // Fall back to current user's branch
        if (! $resolvedBranchId) {
            $resolvedBranchId = (int) (auth()->user()?->branchId() ?? 0);
        }

        // Set the resolved branch
        $data['branch_id'] = $resolvedBranchId ?: 1; // Fallback to branch_id 1 if all else fails

        return $data;
    }
}
