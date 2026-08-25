<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Ponto;
use App\Models\User;

class PontoPolicy
{
    use Traits\ChecksColaboradorAccess;

    public function viewAny(): bool
    {
        return true; // The controller scopes the query
    }

    public function view(User $user, Ponto $ponto): bool
    {
        return $this->canAccessColaborador($user, $ponto->colaborador_id, ['gerenciar-pontos']);
    }

    public function create(User $user, ?int $colaboradorId = null): bool
    {
        if ($colaboradorId === null) {
            return true;
        }

        return $this->canAccessColaborador($user, $colaboradorId, ['gerenciar-pontos']);
    }
}
