<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Justificativa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JustificativaPendenteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Justificativa $justificativa
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $colaborador = $this->justificativa->colaborador;

        return [
            'title' => 'Nova Justificativa Pendente',
            'body' => "O colaborador {$colaborador?->nome} enviou uma justificativa de {$this->justificativa->tipo} para {$this->justificativa->data_referencia->format('d/m/Y')}.",
            'justificativa_id' => $this->justificativa->id,
            'colaborador_id' => $this->justificativa->colaborador_id,
            'type' => 'justificativa_pendente',
        ];
    }
}
