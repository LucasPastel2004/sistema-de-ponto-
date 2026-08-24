<?php

declare(strict_types=1);

namespace App\Policies\Traits;

use App\Models\Colaborador;
use App\Models\User;

trait ChecksColaboradorAccess
{
    protected function canAccessColaborador(User $user, int $colaboradorId, array $permissions): bool
    {
        $userColab = $user->colaborador;
        if (! $userColab) {
            return $user->hasRole('admin');
        }

        if ($colaboradorId === $userColab->id) {
            return true;
        }

        if ($user->hasRole('admin') || $user->hasAnyPermission($permissions)) {
            $targetColab = Colaborador::find($colaboradorId);

            return $targetColab && $targetColab->empresa_id === $userColab->empresa_id;
        }

        return false;
    }
}
