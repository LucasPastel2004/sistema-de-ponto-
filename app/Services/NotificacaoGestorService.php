<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Colaborador;
use App\Models\Justificativa;

class NotificacaoGestorService
{
    public function notificarOmissaoPonto(Colaborador $colaborador): void
    {
        // TODO: Dispatch notification para o gestor informando omissão de batida
        // Notification::send($gestor, new OmissaoPontoNotification($colaborador));
    }

    public function notificarJustificativaPendente(Justificativa $justificativa): void
    {
        // TODO: Dispatch notification para o aprovador/gestor
        // Notification::send($gestor, new JustificativaPendenteNotification($justificativa));
    }

    public function notificarAprovacao(Justificativa $justificativa): void
    {
        // TODO: Dispatch notification para o colaborador informando aprovação/rejeição
        // Notification::send($colaborador, new JustificativaAprovadaNotification($justificativa));
    }
}
