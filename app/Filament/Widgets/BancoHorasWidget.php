<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BancoHorasWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10m';
    protected static bool $isLazy = true;

    public static function canView(): bool
    {
        return auth()->check() 
            && auth()->user()->colaborador !== null 
            && session('view_mode', 'admin') !== 'admin';
    }

    protected function getStats(): array
    {
        $colaborador = auth()->user()->colaborador;

        if (!$colaborador) {
            return [];
        }

        $minutos = $colaborador->saldo_horas ?? 0;
        
        $sinal = $minutos < 0 ? '-' : '+';
        $minutosAbs = abs($minutos);
        $horas = floor($minutosAbs / 60);
        $restanteMinutos = $minutosAbs % 60;
        
        $formatado = sprintf('%s %02dh %02dm', $sinal, $horas, $restanteMinutos);

        $color = 'gray';
        $icon = 'heroicon-m-clock';
        $description = 'Saldo em dia';

        if ($minutos > 0) {
            $color = 'success';
            $icon = 'heroicon-m-arrow-trending-up';
            $description = 'Horas extras acumuladas';
        } elseif ($minutos < 0) {
            $color = 'danger';
            $icon = 'heroicon-m-arrow-trending-down';
            $description = 'Horas devidas';
        }

        return [
            Stat::make('Seu Banco de Horas', $formatado)
                ->description($description)
                ->descriptionIcon($icon)
                ->color($color),
        ];
    }
}
