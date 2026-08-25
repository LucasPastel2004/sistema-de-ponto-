<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Colaborador;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OmissaoPontoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Colaborador $colaborador
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Omissão de Ponto',
            'body' => "O colaborador {$this->colaborador->nome} (matrícula: {$this->colaborador->matricula}) não registrou ponto hoje.",
            'colaborador_id' => $this->colaborador->id,
            'type' => 'omissao_ponto',
        ];
    }
}
