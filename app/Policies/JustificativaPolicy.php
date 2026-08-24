<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Colaborador;
use App\Models\Justificativa;
use App\Models\User;

class JustificativaPolicy
{
    use Traits\ChecksColaboradorAccess;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Justificativa $justificativa): bool
    {
        return $this->canAccessColaborador($user, $justificativa->colaborador_id, ['gerenciar-pontos', 'aprovar-justificativa']);
    }

    public function create(User $user, ?int $colaboradorId = null): bool
    {
        if ($colaboradorId === null) {
            return true;
        }

        return $this->canAccessColaborador($user, $colaboradorId, ['gerenciar-pontos']);
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

        if (! $user->hasPermissionTo('aprovar-justificativa')) {
            return false;
        }

        $userColab = $user->colaborador;
        if (! $userColab) {
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
