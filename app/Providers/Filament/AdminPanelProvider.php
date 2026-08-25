<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login(Login::class)
            ->emailVerification(\App\Filament\Pages\CustomEmailVerificationPrompt::class)
            ->colors([
                'primary' => Color::hex('#E42313'), // Vermelho Unopar
                'danger' => Color::Rose,
                'info' => Color::hex('#003B70'), // Azul Unopar
                'success' => Color::Emerald,
                'warning' => Color::hex('#F36C21'), // Laranja Anhanguera
            ])
            ->font('Outfit')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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
            ])
            ->navigationGroups([
                'Gestão de Ponto',
                'Cadastros',
                'Relatórios',
                'Configurações',
            ])
            ->brandName('Portal Ponto')
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->plugin(
                BreezyCore::make()
                    ->myProfile(
                        shouldRegisterUserMenu: true,
                        shouldRegisterNavigation: false,
                        navigationGroup: 'Configurações',
                        hasAvatars: false,
                        slug: 'meu-perfil',
                    )
                    ->myProfileComponents([
                        'personal_info' => \App\Filament\Pages\CustomPersonalInfo::class,
                    ])
                    ->enableTwoFactorAuthentication(
                        force: false,
                    )
            );
    }
}
