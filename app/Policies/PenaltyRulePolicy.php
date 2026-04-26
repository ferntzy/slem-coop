<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PenaltyRule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PenaltyRulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PenaltyRule');
    }

    public function view(AuthUser $authUser, PenaltyRule $penaltyRule): bool
    {
        return $authUser->can('View:PenaltyRule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PenaltyRule');
    }

    public function update(AuthUser $authUser, PenaltyRule $penaltyRule): bool
    {
        return $authUser->can('Update:PenaltyRule');
    }

    public function delete(AuthUser $authUser, PenaltyRule $penaltyRule): bool
    {
        return $authUser->can('Delete:PenaltyRule');
    }

    public function restore(AuthUser $authUser, PenaltyRule $penaltyRule): bool
    {
        return $authUser->can('Restore:PenaltyRule');
    }

    public function forceDelete(AuthUser $authUser, PenaltyRule $penaltyRule): bool
    {
        return $authUser->can('ForceDelete:PenaltyRule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PenaltyRule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PenaltyRule');
    }

    public function replicate(AuthUser $authUser, PenaltyRule $penaltyRule): bool
    {
        return $authUser->can('Replicate:PenaltyRule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PenaltyRule');
    }
}
