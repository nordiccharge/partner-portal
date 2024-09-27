<?php

namespace App\Providers\Filament;

use App\Filament\Operation\Widgets\StatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use RalphJSmit\Filament\RecordFinder\FilamentRecordFinder;

class OperationPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('operation')
            ->path('operation')
            ->login()
            ->profile()
            ->plugin(FilamentRecordFinder::make())
            ->colors([
                'primary' => Color::hex('#57995A'),
            ])
            ->databaseNotifications()
            ->brandName('Operation Panel')
            ->discoverResources(in: app_path('Filament/Operation/Resources'), for: 'App\\Filament\\Operation\\Resources')
            ->discoverPages(in: app_path('Filament/Operation/Pages'), for: 'App\\Filament\\Operation\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverResources(in: app_path('Filament/Operation/Resources'), for: 'App\\Filament\\Operation\\Resources')
            ->discoverWidgets(in: app_path('Filament/Operation/Widgets'), for: 'App\\Filament\\Operation\\Widgets')
            ->widgets([

            ])
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
                Authenticate::class,
            ]);
    }
}
