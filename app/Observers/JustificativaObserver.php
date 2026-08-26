<?php

namespace App\Observers;

use App\Models\Justificativa;
use App\Enums\StatusJustificativa;
use App\Mail\JustificativaAnalisadaMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class JustificativaObserver
{
    /**
     * Handle the Justificativa "updated" event.
     */
    public function updated(Justificativa $justificativa): void
    {
        // Verifica se o status foi alterado e se agora é Aprovada ou Rejeitada
        if ($justificativa->isDirty('status') && $justificativa->getOriginal('status') === StatusJustificativa::Pendente) {
            
            if (in_array($justificativa->status, [StatusJustificativa::Aprovada, StatusJustificativa::Rejeitada])) {
                
                // Pega o e-mail do colaborador associado
                $email = $justificativa->colaborador?->user?->email;
                
                if ($email) {
                    try {
                        Mail::to($email)->send(new JustificativaAnalisadaMail($justificativa));
                    } catch (\Exception $e) {
                        Log::error("Erro ao enviar e-mail de justificativa para {$email}: " . $e->getMessage());
                    }
                }
            }
        }
    }
}
