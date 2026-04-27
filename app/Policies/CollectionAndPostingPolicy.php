<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\CollectionAndPosting;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollectionAndPostingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CollectionAndPosting');
    }

    public function view(AuthUser $authUser, CollectionAndPosting $collectionAndPosting): bool
    {
        return $authUser->can('View:CollectionAndPosting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CollectionAndPosting');
    }

    public function update(AuthUser $authUser, CollectionAndPosting $collectionAndPosting): bool
    {
        return $authUser->can('Update:CollectionAndPosting');
    }

    public function delete(AuthUser $authUser, CollectionAndPosting $collectionAndPosting): bool
    {
        return $authUser->can('Delete:CollectionAndPosting');
    }

    public function restore(AuthUser $authUser, CollectionAndPosting $collectionAndPosting): bool
    {
        return $authUser->can('Restore:CollectionAndPosting');
    }

    public function forceDelete(AuthUser $authUser, CollectionAndPosting $collectionAndPosting): bool
    {
        return $authUser->can('ForceDelete:CollectionAndPosting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CollectionAndPosting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CollectionAndPosting');
    }

    public function replicate(AuthUser $authUser, CollectionAndPosting $collectionAndPosting): bool
    {
        return $authUser->can('Replicate:CollectionAndPosting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CollectionAndPosting');
    }

}