<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, HasAvatar
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;
    use \Illuminate\Auth\MustVerifyEmail;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar_url',
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

    public function getFilamentAvatarUrl(): ?string
    {
        // Se tiver foto, retorna a URL do storage
        if ($this->avatar_url) {
            return Storage::url($this->avatar_url);
        }

        // SVG padrao (Silhueta) se nao houver foto
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M18.685 19.097A9.723 9.723 0 0021.75 12c0-5.385-4.365-9.75-9.75-9.75S2.25 6.615 2.25 12a9.723 9.723 0 003.065 7.097A9.716 9.716 0 0012 21.75a9.716 9.716 0 006.685-2.653zm-12.54-1.285A7.486 7.486 0 0112 15a7.486 7.486 0 015.855 2.812A8.224 8.224 0 0112 20.25a8.224 8.224 0 01-5.855-2.438zM15.75 9a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" clip-rule="evenodd" /></svg>');
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
