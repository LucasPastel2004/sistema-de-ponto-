<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Justificativa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JustificativaAprovadaNotification extends Notification implements ShouldQueue
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
        $statusLabel = $this->justificativa->status->label();
        $dataRef = $this->justificativa->data_referencia->format('d/m/Y');

        return [
            'title' => "Justificativa {$statusLabel}",
            'body' => "Sua justificativa de {$this->justificativa->tipo} para o dia {$dataRef} foi {$statusLabel}.",
            'justificativa_id' => $this->justificativa->id,
            'status' => $this->justificativa->status->value,
            'type' => 'justificativa_aprovada',
        ];
    }
}
