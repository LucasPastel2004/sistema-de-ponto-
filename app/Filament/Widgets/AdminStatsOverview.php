<?php

namespace App\Filament\Widgets;

use App\Enums\StatusJustificativa;
use App\Models\Colaborador;
use App\Models\Justificativa;
use App\Models\Ponto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return session('view_mode', 'admin') === 'admin' && (auth()->user()?->hasRole('admin') ?? false);
    }

    protected function getStats(): array
    {
        $hoje = Carbon::today();
        
        $totalColaboradores = Colaborador::count();
        
        $pontosHoje = Ponto::whereBetween('registrado_em', [$hoje->copy()->startOfDay(), $hoje->copy()->endOfDay()])->count();
        
        $justificativasPendentes = Justificativa::where('status', StatusJustificativa::Pendente)->count();

        return [
            Stat::make('Total de Colaboradores', $totalColaboradores)
                ->description('Cadastrados no sistema')
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->url(\App\Filament\Resources\ColaboradorResource::getUrl('index')),
                
            Stat::make('Registros de Ponto Hoje', $pontosHoje)
                ->description('Batidas realizadas hoje')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success')
                ->url(\App\Filament\Resources\PontoResource::getUrl('index')),
                
            Stat::make('Justificativas Pendentes', $justificativasPendentes)
                ->description($justificativasPendentes > 0 ? 'Aguardando aprovação' : 'Tudo em dia')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($justificativasPendentes > 0 ? 'warning' : 'success')
                ->url(\App\Filament\Resources\JustificativaResource::getUrl('index')),
        ];
    }
}
