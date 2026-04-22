<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CoopSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CoopSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CoopSetting');
    }

    public function view(AuthUser $authUser, CoopSetting $coopSetting): bool
    {
        return $authUser->can('View:CoopSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CoopSetting');
    }

    public function update(AuthUser $authUser, CoopSetting $coopSetting): bool
    {
        return $authUser->can('Update:CoopSetting');
    }

    public function delete(AuthUser $authUser, CoopSetting $coopSetting): bool
    {
        return $authUser->can('Delete:CoopSetting');
    }

    public function restore(AuthUser $authUser, CoopSetting $coopSetting): bool
    {
        return $authUser->can('Restore:CoopSetting');
    }

    public function forceDelete(AuthUser $authUser, CoopSetting $coopSetting): bool
    {
        return $authUser->can('ForceDelete:CoopSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CoopSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CoopSetting');
    }

    public function replicate(AuthUser $authUser, CoopSetting $coopSetting): bool
    {
        return $authUser->can('Replicate:CoopSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CoopSetting');
    }
}
