<?php

namespace App\Observers;

use App\Models\User;
use App\Services\QrCodeGeneratorService;
use Illuminate\Support\Facades\Storage;

class UserObserver
{
    /**
     * Sync user role based on profile role.
     */
    private function syncUserProfileRole(User $user): void
    {
        $user->loadMissing('profile.role');

        if ($user->profile && $user->profile->role) {
            $user->syncRoles([$user->profile->role->name]);
        } else {
            $user->syncRoles([]);
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->syncUserProfileRole($user);

        app(QrCodeGeneratorService::class)->generateForUser($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->syncUserProfileRole($user);

        // Regenerate QR if needed
        if ($user->wasChanged('user_id') || ! $user->qr_code) {
            app(QrCodeGeneratorService::class)->generateForUser($user);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if ($user->qr_code && Storage::disk('public')->exists($user->qr_code)) {
            Storage::disk('public')->delete($user->qr_code);
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->syncUserProfileRole($user);

        if (! $user->qr_code) {
            app(QrCodeGeneratorService::class)->generateForUser($user);
        }
    }
}
