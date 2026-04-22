<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffDetail;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StaffDetailPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StaffDetail');
    }

    public function view(AuthUser $authUser, StaffDetail $staffDetail): bool
    {
        return $authUser->can('View:StaffDetail');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StaffDetail');
    }

    public function update(AuthUser $authUser, StaffDetail $staffDetail): bool
    {
        return $authUser->can('Update:StaffDetail');
    }

    public function delete(AuthUser $authUser, StaffDetail $staffDetail): bool
    {
        return $authUser->can('Delete:StaffDetail');
    }

    public function restore(AuthUser $authUser, StaffDetail $staffDetail): bool
    {
        return $authUser->can('Restore:StaffDetail');
    }

    public function forceDelete(AuthUser $authUser, StaffDetail $staffDetail): bool
    {
        return $authUser->can('ForceDelete:StaffDetail');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StaffDetail');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StaffDetail');
    }

    public function replicate(AuthUser $authUser, StaffDetail $staffDetail): bool
    {
        return $authUser->can('Replicate:StaffDetail');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StaffDetail');
    }
}
