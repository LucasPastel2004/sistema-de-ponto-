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
            \Filament\View\PanelsRenderHook::HEAD_END,
            function (): string {
                if (! request()->routeIs('filament.*.auth.*')) {
                    return '';
                }
                
                return '<style>
                html { 
                    background-image: linear-gradient(135deg, #00152b, #003366, #007bff, #003366, #00152b) !important; 
                    background-size: 200% 200% !important;
                    animation: gradientPulse 12s ease-in-out infinite;
                    min-height: 100vh; 
                }
                
                @keyframes gradientPulse {
                    0% { background-position: 0% 0%; }
                    50% { background-position: 100% 100%; }
                    100% { background-position: 0% 0%; }
                }
                
                body, main, .fi-simple-main, .fi-simple-page, .fi-simple-layout { 
                    background-color: transparent !important; 
                    background-image: none !important;
                    box-shadow: none !important; 
                    border: none !important; 
                    outline: none !important;
                }
                
                /* Remove any ring utility (Tailwind box-shadow) from the layout */
                main, .fi-simple-main { --tw-ring-shadow: 0 0 #0000 !important; }
                
                /* Text and Logo */
                .fi-logo, h1, h2, h3, label, .fi-form-label, .fi-checkbox-label, span { color: #f8fafc !important; }
                p:not(.fi-fo-field-wrp-error-message) { color: #f8fafc !important; }
                
                /* Error Messages */
                .fi-fo-field-wrp-error-message, .fi-fo-field-wrp-error-message * { color: #ef4444 !important; }
                
                /* Input Fields and Wrappers */
                .fi-input-wrapper, input:not([type="checkbox"]), select {
                    background: rgba(255, 255, 255, 0.95) !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                    color: #111827 !important;
                    border-radius: 0.75rem !important;
                    box-shadow: none !important;
                }
                .fi-input-wrapper input { border: none !important; background: transparent !important; color: #111827 !important; }
                
                input:focus, .fi-input-wrapper:focus-within {
                    border-color: #F15A24 !important;
                    box-shadow: 0 0 0 1px #F15A24 !important;
                }
                
                /* Checkbox Fix */
                input[type="checkbox"] {
                    background-color: transparent !important;
                    border-color: rgba(255, 255, 255, 0.4) !important;
                }
                input[type="checkbox"]:checked {
                    background-color: #F15A24 !important;
                    border-color: #F15A24 !important;
                }
                
                /* Form Card container (Do not change this box color!) */
                main section {
                    background: rgba(255, 255, 255, 0.03) !important;
                    backdrop-filter: blur(16px);
                    border: 1px solid rgba(255, 255, 255, 0.1) !important;
                    border-top: 5px solid #F15A24 !important;
                    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5) !important;
                    border-radius: 1.5rem !important;
                    padding: 2.5rem !important;
                }
                
                /* Button Customization */
                .fi-btn { border-radius: 0.75rem !important; font-weight: bold !important; }
            </style>';
            }
        );
    }
}
