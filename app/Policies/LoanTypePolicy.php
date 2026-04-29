<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\LoanType;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:LoanType');
    }

    public function view(AuthUser $authUser, LoanType $loanType): bool
    {
        return $authUser->can('View:LoanType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:LoanType');
    }

    public function update(AuthUser $authUser, LoanType $loanType): bool
    {
        return $authUser->can('Update:LoanType');
    }

    public function delete(AuthUser $authUser, LoanType $loanType): bool
    {
        return $authUser->can('Delete:LoanType');
    }

    public function restore(AuthUser $authUser, LoanType $loanType): bool
    {
        return $authUser->can('Restore:LoanType');
    }

    public function forceDelete(AuthUser $authUser, LoanType $loanType): bool
    {
        return $authUser->can('ForceDelete:LoanType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:LoanType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:LoanType');
    }

    public function replicate(AuthUser $authUser, LoanType $loanType): bool
    {
        return $authUser->can('Replicate:LoanType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:LoanType');
    }

}