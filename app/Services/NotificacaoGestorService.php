<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Colaborador;
use App\Models\Justificativa;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificacaoGestorService
{
    /**
     * Notifica o gestor/aprovador sobre omissão de batida de ponto por um colaborador.
     * Envia notificação de banco de dados para todos os usuários com permissão 'aprovar-justificativa'
     * da mesma empresa do colaborador.
     */
    public function notificarOmissaoPonto(Colaborador $colaborador): void
    {
        if (! $colaborador->empresa_id) {
            return;
        }

        // Busca gestores da mesma empresa com permissão de aprovar
        $gestores = User::whereHas('colaborador', fn ($q) => $q->where('empresa_id', $colaborador->empresa_id))
            ->permission('aprovar-justificativa')
            ->get();

        foreach ($gestores as $gestor) {
            $gestor->notify(new \App\Notifications\OmissaoPontoNotification($colaborador));
        }
    }

    /**
     * Notifica o(s) aprovador(es) sobre uma nova justificativa pendente de análise.
     */
    public function notificarJustificativaPendente(Justificativa $justificativa): void
    {
        $colaborador = $justificativa->colaborador ?? Colaborador::find($justificativa->colaborador_id);
        if (! $colaborador || ! $colaborador->empresa_id) {
            return;
        }

        $gestores = User::whereHas('colaborador', fn ($q) => $q->where('empresa_id', $colaborador->empresa_id))
            ->permission('aprovar-justificativa')
            ->get();

        foreach ($gestores as $gestor) {
            $gestor->notify(new \App\Notifications\JustificativaPendenteNotification($justificativa));
        }
    }

    /**
     * Notifica o colaborador sobre a aprovação ou rejeição de sua justificativa.
     */
    public function notificarAprovacao(Justificativa $justificativa): void
    {
        $colaborador = $justificativa->colaborador ?? Colaborador::find($justificativa->colaborador_id);
        if (! $colaborador?->user) {
            return;
        }

        $colaborador->user->notify(new \App\Notifications\JustificativaAprovadaNotification($justificativa));
    }
}
