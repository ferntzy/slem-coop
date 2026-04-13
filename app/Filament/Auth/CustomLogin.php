<?php

namespace App\Filament\Auth;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        // Let parent handle the authentication
        $response = parent::authenticate();

        // After successful auth, check if the user account is active
        $user = Filament::auth()->user();

        if ($user && ! $user->is_active) {
            Filament::auth()->logout();

            throw ValidationException::withMessages([
                'data.email' => 'Your account is inactive. Please contact the administrator.',
            ]);
        }

        return $response;
    }
}
