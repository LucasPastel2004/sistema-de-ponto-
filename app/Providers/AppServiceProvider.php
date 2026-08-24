<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
            \Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn (): string => '<style>
                body { background: linear-gradient(135deg, #00152b 0%, #003366 100%) !important; }
                main { background: transparent !important; }
                .fi-simple-main-content { 
                    background: rgba(255, 255, 255, 0.97) !important; 
                    border-top: 6px solid #F15A24 !important; 
                    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important; 
                    border-radius: 1.5rem !important; 
                }
                .dark body { background: linear-gradient(135deg, #0f172a 0%, #020617 100%) !important; }
                .dark .fi-simple-main-content { background: rgba(30, 41, 59, 0.9) !important; border-top: 6px solid #F15A24 !important; }
            </style>'
        );
    }
}
