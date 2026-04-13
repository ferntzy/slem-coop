<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ProfileEmailUserProvider extends EloquentUserProvider
{
    public function retrieveByCredentials(array $credentials)
    {
        $email = $credentials['email'] ?? null;

        if (! $email) {
            return null;
        }

        return User::query()
            ->whereHas('profile', fn ($q) => $q->where('email', $email))
            ->first();
    }
}
