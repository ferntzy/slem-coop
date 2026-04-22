<?php

namespace App\Observers;

use App\Models\Profile;

class ProfileObserver
{
    private function syncUserRole(Profile $profile): void
    {
        if ($profile->user) {
            if ($profile->role) {
                // Remove all current 'web' guard roles first, then assign the new one
                $currentRoles = $profile->user->roles()->where('guard_name', 'web')->pluck('name')->toArray();
                foreach ($currentRoles as $roleName) {
                    $profile->user->removeRole($roleName);
                }
                $profile->user->assignRole($profile->role->name);
            } else {
                $currentRoles = $profile->user->roles()->where('guard_name', 'web')->pluck('name')->toArray();
                foreach ($currentRoles as $roleName) {
                    $profile->user->removeRole($roleName);
                }
            }
        }
    }

    /**
     * Handle the Profile "created" event.
     */
    public function created(Profile $profile): void
    {
        $this->syncUserRole($profile);
    }

    /**
     * Handle the Profile "updated" event.
     */
    public function updated(Profile $profile): void
    {
        $this->syncUserRole($profile);
    }

    /**
     * Handle the Profile "deleted" event.
     */
    public function deleted(Profile $profile): void
    {
        // When a profile is deleted, we could theoretically strip roles from user,
        // but typically user is deleted first anyway because of foreign keys.
    }

    /**
     * Handle the Profile "restored" event.
     */
    public function restored(Profile $profile): void
    {
        $this->syncUserRole($profile);
    }

    /**
     * Handle the Profile "force deleted" event.
     */
    public function forceDeleted(Profile $profile): void
    {
        //
    }
}
