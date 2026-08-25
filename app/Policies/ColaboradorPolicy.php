<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Colaborador;
use App\Models\User;

class ColaboradorPolicy
{
    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, Colaborador $colaborador): bool
    {
        $userColab = $user->colaborador;
        if (! $userColab) {
            return $user->hasRole('admin');
        }

        if ($colaborador->id === $userColab->id) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasPermissionTo('gerenciar-pontos') || $user->hasPermissionTo('aprovar-justificativa')) {
            return $colaborador->empresa_id === $userColab->empresa_id;
        }

        return false;
    }
}

