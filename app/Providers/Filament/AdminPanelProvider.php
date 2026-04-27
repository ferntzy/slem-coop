<?php

namespace App\Providers\Filament;

use App\Filament\Auth\CustomLogin;
use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\CollectionsChart;
use App\Filament\Widgets\CollectionsTodayWidget;
use App\Filament\Widgets\LoanApplicationsChart;
use App\Filament\Widgets\LoanPortfolioChart;
use App\Filament\Widgets\MemberGreetingWidget;
use App\Filament\Widgets\MemberLoanScheduleWidget;
use App\Filament\Widgets\MemberLoanStatusChart;
use App\Filament\Widgets\MemberPaymentHistoryChart;
use App\Filament\Widgets\MemberRecentTransactionsWidget;
use App\Filament\Widgets\MemberStatusChart;
use App\Filament\Widgets\MemberUpcomingDueDatesWidget;
use App\Filament\Widgets\RecentLoanApplicationsWidget;
use App\Filament\Widgets\ShareCapitalChart;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Http\Middleware\CheckUserIsActive;
use App\Models\SystemSetting;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
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
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->brandLogo(new HtmlString('
    <div class="flex items-center gap-2">
        <img src="'.asset('logo-dark.png').'" alt="Logo" class="h-8" />

        <span class="text-xl font-normal leading-5 tracking-tight dark:text-white">
            SLEM
        </span>

        <span class="text-xl font-bold leading-5 tracking-tight text-green-600">
            COOP
        </span>
    </div>
'))
            ->brandLogoHeight('2rem')

            ->favicon(function () {
                // Prevent crash during migrations
                if (! Schema::hasTable('system_settings')) {
                    return asset('logo-dark.png');
                }
                $favicon = SystemSetting::get('favicon');

                return $favicon
                    ? Storage::disk('public')->url($favicon)
                    : asset('logo-dark.png');
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
                        'sm' => 2,
                        'lg' => 3,
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
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

                MemberGreetingWidget::class,
                StatsOverviewWidget::class,
                MemberLoanStatusChart::class,
                MemberPaymentHistoryChart::class,
                MemberRecentTransactionsWidget::class,
                MemberUpcomingDueDatesWidget::class,
                ShareCapitalChart::class,
                MemberLoanScheduleWidget::class,
                CollectionsChart::class,
                LoanApplicationsChart::class,
                MemberStatusChart::class,
                LoanPortfolioChart::class,
                CollectionsTodayWidget::class,
                RecentLoanApplicationsWidget::class,
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
                'Payment',
                'Reports',
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
