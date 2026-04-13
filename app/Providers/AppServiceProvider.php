<?php

namespace App\Providers;

use App\Auth\ProfileEmailUserProvider;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Models\DailyCollectionEntry;
use App\Models\LoanApplicationDocument;
use App\Models\MembershipApplication;
use App\Models\Profile;
use App\Models\ShareCapitalTransaction;
use App\Models\User;
use App\Observers\DailyCollectionEntryObserver;
use App\Observers\LoanApplicationDocumentObserver;
use App\Observers\MembershipApplicationObserver;
use App\Observers\ProfileObserver;
use App\Observers\ShareCapitalTransactionObserver;
use App\Observers\UserObserver;
use App\Services\CertificateService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(CertificateService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('profile-email', function ($app, array $config) {
            return new ProfileEmailUserProvider(
                $app['hash'],
                $config['model'],
            );
        });

        // Register observer for Share Capital transactions
        ShareCapitalTransaction::observe(ShareCapitalTransactionObserver::class);
        Profile::observe(ProfileObserver::class);
        User::observe(UserObserver::class);
        MembershipApplication::observe(MembershipApplicationObserver::class);
        DailyCollectionEntry::observe(DailyCollectionEntryObserver::class);
        LoanApplicationDocument::observe(LoanApplicationDocumentObserver::class);
    }
}
