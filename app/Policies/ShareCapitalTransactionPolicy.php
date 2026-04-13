<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ShareCapitalTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShareCapitalTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ShareCapitalTransaction');
    }

    public function view(AuthUser $authUser, ShareCapitalTransaction $shareCapitalTransaction): bool
    {
        return $authUser->can('View:ShareCapitalTransaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ShareCapitalTransaction');
    }

    public function update(AuthUser $authUser, ShareCapitalTransaction $shareCapitalTransaction): bool
    {
        return $authUser->can('Update:ShareCapitalTransaction');
    }

    public function delete(AuthUser $authUser, ShareCapitalTransaction $shareCapitalTransaction): bool
    {
        return $authUser->can('Delete:ShareCapitalTransaction');
    }

    public function restore(AuthUser $authUser, ShareCapitalTransaction $shareCapitalTransaction): bool
    {
        return $authUser->can('Restore:ShareCapitalTransaction');
    }

    public function forceDelete(AuthUser $authUser, ShareCapitalTransaction $shareCapitalTransaction): bool
    {
        return $authUser->can('ForceDelete:ShareCapitalTransaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ShareCapitalTransaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ShareCapitalTransaction');
    }

    public function replicate(AuthUser $authUser, ShareCapitalTransaction $shareCapitalTransaction): bool
    {
        return $authUser->can('Replicate:ShareCapitalTransaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ShareCapitalTransaction');
    }

}