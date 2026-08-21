<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Justificativa;
use App\Models\User;
use App\Models\Colaborador;

class JustificativaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Justificativa $justificativa): bool
    {
        $userColab = $user->colaborador;
        if (!$userColab) {
            return $user->hasRole('admin');
        }

        if ($justificativa->colaborador_id === $userColab->id) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasPermissionTo('gerenciar-pontos') || $user->hasPermissionTo('aprovar-justificativa')) {
            $targetColab = $justificativa->colaborador ?? Colaborador::find($justificativa->colaborador_id);
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

    public function aprovar(User $user, Justificativa $justificativa): bool
    {
        return $this->canManageJustificativa($user, $justificativa);
    }

    public function rejeitar(User $user, Justificativa $justificativa): bool
    {
        return $this->canManageJustificativa($user, $justificativa);
    }

    private function canManageJustificativa(User $user, Justificativa $justificativa): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (!$user->hasPermissionTo('aprovar-justificativa')) {
            return false;
        }

        $userColab = $user->colaborador;
        if (!$userColab) {
            return false;
        }

        // Não pode aprovar a própria justificativa
        if ($justificativa->colaborador_id === $userColab->id) {
            return false;
        }

        $targetColab = $justificativa->colaborador ?? Colaborador::find($justificativa->colaborador_id);
        return $targetColab && $targetColab->empresa_id === $userColab->empresa_id;
    }
}
