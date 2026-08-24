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
                html { background: linear-gradient(135deg, #00152b 0%, #003366 100%) !important; min-height: 100vh; overflow-x: hidden; }
                body, main, .fi-simple-main, .fi-simple-page, .fi-simple-layout { 
                    background: transparent !important; 
                    box-shadow: none !important; 
                    border: none !important; 
                    outline: none !important;
                }
                
                /* Animated Background Blobs */
                html::before, html::after {
                    content: "";
                    position: fixed;
                    border-radius: 50%;
                    filter: blur(120px);
                    z-index: -1;
                    pointer-events: none;
                }
                html::before {
                    width: 600px; height: 600px;
                    background: rgba(241, 90, 36, 0.30); /* Unopar Orange */
                    top: -10%; left: -10%;
                    animation: floatBlob1 25s infinite ease-in-out;
                }
                html::after {
                    width: 500px; height: 500px;
                    background: rgba(241, 90, 36, 0.25);
                    bottom: -10%; right: -10%;
                    animation: floatBlob2 30s infinite ease-in-out;
                }
                
                @keyframes floatBlob1 {
                    0% { transform: translate(0vw, 0vh) scale(1); }
                    33% { transform: translate(50vw, 20vh) scale(1.2); }
                    66% { transform: translate(20vw, 60vh) scale(0.8); }
                    100% { transform: translate(0vw, 0vh) scale(1); }
                }
                
                @keyframes floatBlob2 {
                    0% { transform: translate(0vw, 0vh) scale(1); }
                    33% { transform: translate(-40vw, -40vh) scale(1.3); }
                    66% { transform: translate(-60vw, -10vh) scale(0.9); }
                    100% { transform: translate(0vw, 0vh) scale(1); }
                }
                
                /* Remove any ring utility (Tailwind box-shadow) from the layout */
                main, .fi-simple-main { --tw-ring-shadow: 0 0 #0000 !important; }
                
                /* Text and Logo */
                .fi-logo, h1, h2, h3, p, label, .fi-form-label, span { color: #f8fafc !important; }
                
                /* Input Fields and Wrappers */
                .fi-input-wrapper, input, select {
                    background: rgba(255, 255, 255, 0.05) !important;
                    border: 1px solid rgba(255, 255, 255, 0.2) !important;
                    color: white !important;
                    border-radius: 0.75rem !important;
                    box-shadow: none !important;
                }
                .fi-input-wrapper input { border: none !important; background: transparent !important; }
                .fi-input-wrapper * { color: white !important; }
                
                input:focus, .fi-input-wrapper:focus-within {
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
