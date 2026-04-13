<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Pages\Dashboard;
use App\Models\SystemSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Http\Middleware\CheckUserIsActive;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Icons\Heroicon;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('coop')
            ->authGuard('web')
            ->login(CustomLogin::class)
            ->globalSearch(false)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandLogo(fn () => view('filament.brand'))
            ->brandLogoHeight('2rem')

            ->favicon(function () {
                // Prevent crash during migrations
                if (!Schema::hasTable('system_settings')) {
                    return asset('images/my-logo.svg');
                }
                $favicon = SystemSetting::get('favicon');
                return $favicon
                    ? Storage::disk('public')->url($favicon)
                    : asset('images/my-logo.svg');

            })

           ->renderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn () => view('filament.Notification'),
        )
            // Plugins
            ->plugins([
                FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm'      => 2,
                        'lg'      => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm'      => 2,
                        'lg'      => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm'      => 2,
                    ]),
            ])


            ->colors([
                'primary' => Color::hex(
                    $this->getPrimaryColor()
                ),
            ])

            // Resources, Pages, Widgets
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,  // Custom dashboard with role-based widgets
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([

                \App\Filament\Widgets\MemberGreetingWidget::class,
                \App\Filament\Widgets\StatsOverviewWidget::class,
                \App\Filament\Widgets\MemberLoanStatusChart::class,
                \App\Filament\Widgets\MemberPaymentHistoryChart::class,
                \App\Filament\Widgets\MemberRecentTransactionsWidget::class,
                \App\Filament\Widgets\MemberUpcomingDueDatesWidget::class,
                \App\Filament\Widgets\ShareCapitalChart::class,
                \App\Filament\Widgets\MemberLoanScheduleWidget::class,
                \App\Filament\Widgets\CollectionsChart::class,
                \App\Filament\Widgets\LoanApplicationsChart::class,
                \App\Filament\Widgets\MemberStatusChart::class,
                \App\Filament\Widgets\LoanPortfolioChart::class,
                \App\Filament\Widgets\CollectionsTodayWidget::class,
                \App\Filament\Widgets\RecentLoanApplicationsWidget::class,
            ])

            // Middleware
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                // first run the default Filament authentication check
                Authenticate::class,

                // ensure the authenticated user is still active
                CheckUserIsActive::class,
            ])
            ->navigationGroups([
            'Dashboard',
            'Membership Management',
            'Loan Management',
            'Payment Management',
            'Share Capital',
            'User Management',
            'Pages',
            'Savings Management',
            'Settings',
            ]);
    }

    private function getPrimaryColor(): string
    {
        try {
            if (Schema::hasTable('system_settings')) {
                $color = SystemSetting::get('primary_color');
                return $color ?: '#0d9488';
            }
        } catch (\Exception $e) {
            // Silently catch any database errors during bootstrap
        }

        return '#0d9488';
    }
}
