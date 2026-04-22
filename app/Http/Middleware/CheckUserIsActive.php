<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Routing\Route;

class CheckUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * If the user is authenticated but not active, log them out and redirect
     * back to the login page with an error message.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && ! $user->is_active) {
            Auth::logout();

            // Filament panels define their own login route name, so we
            // attempt to use the panel helper. If the facade isn't
            // available (e.g. during a normal web request with no
            // Filament installed) fall back to the conventional route.
            $loginUrl = null;
            if (class_exists(Filament::class)) {
                $loginUrl = Filament::getLoginUrl();
            }

            if (! $loginUrl && Route::has('login')) {
                $loginUrl = route('login');
            }

            return redirect($loginUrl ?? '/')
                ->with('message', 'Your account is inactive.');
        }

        return $next($request);
    }
}
