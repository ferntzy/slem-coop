<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\RestructureApplication;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RestructureApplicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RestructureApplication');
    }

    public function view(AuthUser $authUser, RestructureApplication $restructureApplication): bool
    {
        return $authUser->can('View:RestructureApplication');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RestructureApplication');
    }

    public function update(AuthUser $authUser, RestructureApplication $restructureApplication): bool
    {
        return $authUser->can('Update:RestructureApplication');
    }

    public function delete(AuthUser $authUser, RestructureApplication $restructureApplication): bool
    {
        return $authUser->can('Delete:RestructureApplication');
    }

    public function restore(AuthUser $authUser, RestructureApplication $restructureApplication): bool
    {
        return $authUser->can('Restore:RestructureApplication');
    }

    public function forceDelete(AuthUser $authUser, RestructureApplication $restructureApplication): bool
    {
        return $authUser->can('ForceDelete:RestructureApplication');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RestructureApplication');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RestructureApplication');
    }

    public function replicate(AuthUser $authUser, RestructureApplication $restructureApplication): bool
    {
        return $authUser->can('Replicate:RestructureApplication');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RestructureApplication');
    }
}
