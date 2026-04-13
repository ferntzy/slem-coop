<?php

namespace App\Http\Responses;

use App\Support\RoleRedirectMap;
use Filament\Facades\Filament;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        $user = Filament::auth()->user();
        
        $role = $user?->roles?->first()?->name;

        return redirect()->intended(RoleRedirectMap::getRedirect($role));
    }
}
