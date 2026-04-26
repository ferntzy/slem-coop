<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SavingsAccount;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SavingsAccountPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SavingsAccount');
    }

    public function view(AuthUser $authUser, SavingsAccount $savingsAccount): bool
    {
        return $authUser->can('View:SavingsAccount');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SavingsAccount');
    }

    public function update(AuthUser $authUser, SavingsAccount $savingsAccount): bool
    {
        return $authUser->can('Update:SavingsAccount');
    }

    public function delete(AuthUser $authUser, SavingsAccount $savingsAccount): bool
    {
        return $authUser->can('Delete:SavingsAccount');
    }

    public function restore(AuthUser $authUser, SavingsAccount $savingsAccount): bool
    {
        return $authUser->can('Restore:SavingsAccount');
    }

    public function forceDelete(AuthUser $authUser, SavingsAccount $savingsAccount): bool
    {
        return $authUser->can('ForceDelete:SavingsAccount');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SavingsAccount');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SavingsAccount');
    }

    public function replicate(AuthUser $authUser, SavingsAccount $savingsAccount): bool
    {
        return $authUser->can('Replicate:SavingsAccount');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SavingsAccount');
    }
}
