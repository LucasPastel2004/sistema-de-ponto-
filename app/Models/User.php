<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, TwoFactorAuthenticatable;
    use \Illuminate\Auth\MustVerifyEmail;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Todos os usuários autênticados podem acessar o painel unificado.
     * As restrições de tela (Admin vs Colaborador) são feitas pelas Policies.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function colaborador(): HasOne
    {
        return $this->hasOne(Colaborador::class);
    }

    /**
     * Determine if the user has verified their email address.
     * If they don't have an email (only a username), we consider it verified to not block login.
     */
    public function hasVerifiedEmail()
    {
        if (empty($this->email)) {
            return true;
        }

        return ! is_null($this->email_verified_at);
    }
}
