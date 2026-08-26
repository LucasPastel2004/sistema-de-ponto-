<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use App\Models\Ponto;
use App\Observers\PontoObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Ponto::observe(PontoObserver::class);
        \App\Models\Justificativa::observe(\App\Observers\JustificativaObserver::class);

        VerifyEmail::createUrlUsing(function ($notifiable) {
            return URL::temporarySignedRoute(
                'custom.verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => hash('sha256', $notifiable->getEmailForVerification()),
                ]
            );
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        } else {
            Model::shouldBeStrict();
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').$request->ip());
        });

        \Filament\Support\Facades\FilamentAsset::register([
            \Filament\Support\Assets\Css::make('custom-css', asset('css/custom.css')),
        ]);

        \Filament\Support\Facades\FilamentView::registerRenderHook(
            \Filament\View\PanelsRenderHook::HEAD_END,
            function (): string {
                if (! request()->routeIs('filament.*.auth.*')) {
                    return '';
                }

                return '<style>
                html {
                    background-image: linear-gradient(135deg, #000000, #111111, #222222, #111111, #000000) !important;
                    background-size: 200% 200% !important;
                    animation: gradientPulse 12s ease-in-out infinite;
                    min-height: 100vh;
                }

                @keyframes gradientPulse {
                    0% { background-position: 0% 0%; }
                    50% { background-position: 100% 100%; }
                    100% { background-position: 0% 0%; }
                }

                body, .fi-layout, .fi-main, .fi-simple-main, .fi-simple-page, .fi-simple-layout {
                    background-color: transparent !important;
                    background-image: none !important;
                }

                /* Oculta ring/sombras padrões de fundo do Filament */
                .fi-main, .fi-simple-main { --tw-ring-shadow: 0 0 #0000 !important; }

                /* Efeito de Vidro (Frosted Glass) para os Cards do sistema e da página de Auth */
                .fi-section, .fi-wi-stats-overview-stat, .fi-modal-window, .fi-simple-main section, .fi-ta-content {
                    background: rgba(255, 255, 255, 0.05) !important;
                    backdrop-filter: blur(16px);
                    -webkit-backdrop-filter: blur(16px);
                    border: 1px solid rgba(255, 255, 255, 0.1) !important;
                    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important;
                    border-radius: 1rem !important;
                }

                /* Mais espaço interno na box central de Auth */
                .fi-simple-main section {
                    padding: 48px !important;
                }

                /* Sidebar e Topbar com vidro fosco mais escuro para não brigar com o fundo */
                .fi-sidebar, .fi-topbar {
                    background: rgba(0, 21, 43, 0.6) !important;
                    backdrop-filter: blur(12px);
                    -webkit-backdrop-filter: blur(12px);
                    border-color: rgba(255, 255, 255, 0.1) !important;
                }

                /* Arredondamento dos inputs para manter a estética */
                .fi-input-wrapper, input, select {
                    border-radius: 0.75rem !important;
                }

                input:focus, .fi-input-wrapper:focus-within {
                    border-color: #F15A24 !important;
                    box-shadow: 0 0 0 1px #F15A24 !important;
                }

                /* Arredondamento dos botões */
                .fi-btn { border-radius: 0.75rem !important; font-weight: bold !important; }

                /* Oculta texto quebrado "Avat" do avatar (global) */
                .fi-user-avatar { color: transparent !important; text-indent: -9999px; }
            </style>';
            }
        );
    }
}
