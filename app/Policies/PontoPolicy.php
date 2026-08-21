<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ponto;
use App\Models\User;
use App\Models\Colaborador;

class PontoPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // The controller scopes the query
    }

    public function view(User $user, Ponto $ponto): bool
    {
        $userColab = $user->colaborador;
        if (!$userColab) {
            return $user->hasRole('admin');
        }

        if ($ponto->colaborador_id === $userColab->id) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasPermissionTo('gerenciar-pontos')) {
            $targetColab = $ponto->colaborador ?? Colaborador::find($ponto->colaborador_id);
            return $targetColab && $targetColab->empresa_id === $userColab->empresa_id;
        }

        return false;
    }

    public function create(User $user, int $colaboradorId): bool
    {
        $userColab = $user->colaborador;
        if (!$userColab) {
            return $user->hasRole('admin');
        }

        if ($colaboradorId === $userColab->id) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasPermissionTo('gerenciar-pontos')) {
            $targetColab = Colaborador::find($colaboradorId);
            return $targetColab && $targetColab->empresa_id === $userColab->empresa_id;
        }

        return false;
    }
}
