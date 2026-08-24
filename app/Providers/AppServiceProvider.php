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
                html { background: linear-gradient(135deg, #00152b 0%, #003366 100%) !important; min-height: 100vh; }
                body, main, .fi-simple-main, .fi-simple-page { background: transparent !important; }
                
                /* Text and Logo */
                .fi-logo, h1, h2, h3, p, label, .fi-form-label, span { color: #f8fafc !important; }
                
                /* Input Fields */
                input, select {
                    background: rgba(255, 255, 255, 0.05) !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                    color: white !important;
                    border-radius: 0.75rem !important;
                }
                input:focus {
                    border-color: #F15A24 !important;
                    box-shadow: 0 0 0 1px #F15A24 !important;
                }
                
                /* Form Card container in Filament 3 (usually a section inside main) */
                main section {
                    background: rgba(255, 255, 255, 0.03) !important;
                    backdrop-filter: blur(16px);
                    border: 1px solid rgba(255, 255, 255, 0.1) !important;
                    border-top: 5px solid #F15A24 !important;
                    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important;
                    border-radius: 1.5rem !important;
                    padding: 2.5rem !important;
                }
                
                /* Button Customization is already okay but let us ensure it stands out */
                .fi-btn { border-radius: 0.75rem !important; font-weight: bold !important; }
            </style>'
        );
    }
}
