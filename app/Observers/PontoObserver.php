<?php

namespace App\Observers;

use App\Models\Ponto;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class PontoObserver
{
    private function reprocessarSeNecessario(Ponto $ponto): void
    {
        $dataPonto = Carbon::parse($ponto->registrado_em)->startOfDay();
        
        // Se a data for de ontem para trás, chamamos o reprocessamento
        if ($dataPonto->isPast() && !$dataPonto->isToday()) {
            Artisan::call('ponto:fechamento-diario', [
                'data' => $dataPonto->format('Y-m-d'),
                '--colaborador' => $ponto->colaborador_id,
            ]);
        }
    }

    public function created(Ponto $ponto): void
    {
        $this->reprocessarSeNecessario($ponto);
    }

    public function updated(Ponto $ponto): void
    {
        $this->reprocessarSeNecessario($ponto);
        
        // Em caso de mudança de data (ex: de dia 10 para dia 11), reprocessa o dia antigo também
        if ($ponto->isDirty('registrado_em')) {
            $dataAntiga = Carbon::parse($ponto->getOriginal('registrado_em'))->startOfDay();
            if ($dataAntiga->isPast() && !$dataAntiga->isToday() && $dataAntiga->notEqualTo(Carbon::parse($ponto->registrado_em)->startOfDay())) {
                Artisan::call('ponto:fechamento-diario', [
                    'data' => $dataAntiga->format('Y-m-d'),
                    '--colaborador' => $ponto->colaborador_id,
                ]);
            }
        }
    }

    public function deleted(Ponto $ponto): void
    {
        $this->reprocessarSeNecessario($ponto);
    }

    public function restored(Ponto $ponto): void
    {
        $this->reprocessarSeNecessario($ponto);
    }

    public function forceDeleted(Ponto $ponto): void
    {
        $this->reprocessarSeNecessario($ponto);
    }
}
