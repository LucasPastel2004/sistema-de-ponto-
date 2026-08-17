<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Colaborador;
use App\Models\Justificativa;
use App\Models\Ponto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumoJornadaWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $hoje = today();

        $batidasHoje = Ponto::whereDate('registrado_em', $hoje)->count();
        
        $presentes = Ponto::whereDate('registrado_em', $hoje)
            ->distinct('colaborador_id')
            ->count('colaborador_id');
            
        $justificativasPendentes = Justificativa::where('status', \App\Enums\StatusJustificativa::Pendente)->count();
        
        // Alertas de omissão (Colaboradores ativos sem ponto hoje)
        $colaboradoresComPonto = Ponto::whereDate('registrado_em', $hoje)->pluck('colaborador_id');
        $omissao = Colaborador::where('ativo', true)
            ->whereNotIn('id', $colaboradoresComPonto)
            ->count();

        return [
            Stat::make('Total de Batidas Hoje', $batidasHoje)
                ->description('Registros efetuados hoje')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Colaboradores Presentes', $presentes)
                ->description('Em jornada')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Justificativas Pendentes', $justificativasPendentes)
                ->description('Aguardando aprovação')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Alertas de Omissão', $omissao)
                ->description('Sem registro hoje')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
