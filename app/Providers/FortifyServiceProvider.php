<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

/**
 * FortifyServiceProvider — Escopo: API REST APENAS.
 *
 * O Fortify é responsável exclusivamente pela autenticação e MFA
 * nas rotas da API REST (/api/v1/*). O painel administrativo
 * FilamentPHP utiliza o plugin filament-breezy para gerenciar
 * seu próprio fluxo de 2FA (TOTP), evitando conflitos de
 * middleware e views entre os dois ecossistemas.
 *
 * Fronteiras de responsabilidade:
 * ┌─────────────────────────┬──────────────────────────────┐
 * │ Fortify                 │ Filament Breezy              │
 * ├─────────────────────────┼──────────────────────────────┤
 * │ API login/logout        │ Painel /admin login          │
 * │ API 2FA challenge       │ Painel 2FA (TOTP nativo)     │
 * │ API password reset      │ Painel profile management    │
 * │ Rate limiting em /login │ Filament auth middleware     │
 * └─────────────────────────┴──────────────────────────────┘
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Desabilita as views do Fortify — modo headless para API
        // O Filament gerencia suas próprias views de autenticação.
        config([
            'fortify.views' => false,
        ]);
    }

    public function boot(): void
    {
        // ─── Autenticação customizada para API ─────────────────────────
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        // ─── Rate Limiting para login via API ──────────────────────────
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = str($request->input('email'))->lower() . '|' . $request->ip();

            return Limit::perMinute(5)->by($throttleKey);
        });

        // ─── 2FA Challenge via API (headless, sem views) ───────────────
        // Retorna null para indicar modo headless — a API REST devolve
        // JSON pedindo o código TOTP; o frontend consome via AJAX.
        Fortify::twoFactorChallengeView(function () {
            return null;
        });
    }
}
