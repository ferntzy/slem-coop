<?php

namespace App\Http\Responses;

use App\Support\RoleRedirectMap;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Facades\Filament;
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
