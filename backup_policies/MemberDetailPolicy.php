<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MemberDetail;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class MemberDetailPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MemberDetail');
    }

    public function view(AuthUser $authUser, MemberDetail $memberDetail): bool
    {
        return $authUser->can('View:MemberDetail');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MemberDetail');
    }

    public function update(AuthUser $authUser, MemberDetail $memberDetail): bool
    {
        return $authUser->can('Update:MemberDetail');
    }

    public function delete(AuthUser $authUser, MemberDetail $memberDetail): bool
    {
        return $authUser->can('Delete:MemberDetail');
    }

    public function restore(AuthUser $authUser, MemberDetail $memberDetail): bool
    {
        return $authUser->can('Restore:MemberDetail');
    }

    public function forceDelete(AuthUser $authUser, MemberDetail $memberDetail): bool
    {
        return $authUser->can('ForceDelete:MemberDetail');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MemberDetail');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MemberDetail');
    }

    public function replicate(AuthUser $authUser, MemberDetail $memberDetail): bool
    {
        return $authUser->can('Replicate:MemberDetail');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MemberDetail');
    }
}
