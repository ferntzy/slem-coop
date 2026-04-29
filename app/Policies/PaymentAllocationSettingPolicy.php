<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PaymentAllocationSetting;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentAllocationSettingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PaymentAllocationSetting');
    }

    public function view(AuthUser $authUser, PaymentAllocationSetting $paymentAllocationSetting): bool
    {
        return $authUser->can('View:PaymentAllocationSetting');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PaymentAllocationSetting');
    }

    public function update(AuthUser $authUser, PaymentAllocationSetting $paymentAllocationSetting): bool
    {
        return $authUser->can('Update:PaymentAllocationSetting');
    }

    public function delete(AuthUser $authUser, PaymentAllocationSetting $paymentAllocationSetting): bool
    {
        return $authUser->can('Delete:PaymentAllocationSetting');
    }

    public function restore(AuthUser $authUser, PaymentAllocationSetting $paymentAllocationSetting): bool
    {
        return $authUser->can('Restore:PaymentAllocationSetting');
    }

    public function forceDelete(AuthUser $authUser, PaymentAllocationSetting $paymentAllocationSetting): bool
    {
        return $authUser->can('ForceDelete:PaymentAllocationSetting');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PaymentAllocationSetting');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PaymentAllocationSetting');
    }

    public function replicate(AuthUser $authUser, PaymentAllocationSetting $paymentAllocationSetting): bool
    {
        return $authUser->can('Replicate:PaymentAllocationSetting');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PaymentAllocationSetting');
    }

}