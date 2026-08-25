<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\StatusJustificativa;
use App\Models\Colaborador;
use App\Models\Justificativa;
use App\Models\Ponto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ResumoJornadaWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '5m';
    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }
        
        $colaboradorModel = $user->colaborador;
        $isAdminMode = session('view_mode', 'admin') === 'admin' && ($user->hasRole('admin') ?? false);
        $empresaId = $isAdminMode ? null : $colaboradorModel?->empresa_id;

        $hoje = today();
        $inicioDia = $hoje->copy()->startOfDay();
        $fimDia = $hoje->copy()->endOfDay();

        $cacheSufix = $empresaId ?? 'admin';
        $ttl = now()->addMinutes(2);

        $batidasHoje = Cache::remember("dash_batidas_hoje_{$cacheSufix}", $ttl, function () use ($inicioDia, $fimDia, $empresaId) {
            return Ponto::whereBetween('registrado_em', [$inicioDia, $fimDia])
                ->when($empresaId, fn($q) => $q->whereHas('colaborador', fn($q2) => $q2->where('empresa_id', $empresaId)))
                ->count();
        });

        $presentes = Cache::remember("dash_presentes_{$cacheSufix}", $ttl, function () use ($inicioDia, $fimDia, $empresaId) {
            return Ponto::whereBetween('registrado_em', [$inicioDia, $fimDia])
                ->when($empresaId, fn($q) => $q->whereHas('colaborador', fn($q2) => $q2->where('empresa_id', $empresaId)))
                ->distinct('colaborador_id')
                ->count('colaborador_id');
        });

        $justificativasPendentes = Cache::remember("dash_justif_{$cacheSufix}", $ttl, function () use ($empresaId) {
            return Justificativa::where('status', StatusJustificativa::Pendente)
                ->when($empresaId, fn($q) => $q->whereHas('colaborador', fn($q2) => $q2->where('empresa_id', $empresaId)))
                ->count();
        });

        // Alertas de omissão
        $omissao = Cache::remember("dash_omissao_{$cacheSufix}", $ttl, function () use ($inicioDia, $fimDia, $empresaId) {
            return Colaborador::where('ativo', true)
                ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
                ->whereDoesntHave('pontos', function ($q) use ($inicioDia, $fimDia) {
                    $q->whereBetween('registrado_em', [$inicioDia, $fimDia]);
                })
                ->count();
        });

        return [
            Stat::make('Total de Batidas Hoje', $batidasHoje)
                ->description('Registros efetuados hoje')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success')
                ->url(\App\Filament\Resources\PontoResource::getUrl('index') . '?tableFilters[registrado_em][registrado_de]=' . today()->format('Y-m-d') . '&tableFilters[registrado_em][registrado_ate]=' . today()->format('Y-m-d')),
            Stat::make('Colaboradores Presentes', $presentes)
                ->description('Em jornada')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->url(\App\Filament\Resources\ColaboradorResource::getUrl('index') . '?tableFilters[presenca_hoje][value]=1'),
            Stat::make('Justificativas Pendentes', $justificativasPendentes)
                ->description('Aguardando aprovação')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(\App\Filament\Resources\JustificativaResource::getUrl('index') . '?tableFilters[status][value]=pendente'),
            Stat::make('Alertas de Omissão', $omissao)
                ->description('Sem registro hoje')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger')
                ->url(\App\Filament\Resources\ColaboradorResource::getUrl('index') . '?tableFilters[presenca_hoje][value]=0'),
        ];
    }
}
