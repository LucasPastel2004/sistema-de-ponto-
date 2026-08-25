<?php

namespace App\Filament\Widgets;

use App\Models\Ponto;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PontosPorDiaChart extends ChartWidget
{
    protected static ?string $heading = 'Registros de Ponto (Últimos 7 Dias)';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return session('view_mode', 'admin') === 'admin' && (auth()->user()?->hasRole('admin') ?? false);
    }

    protected function getData(): array
    {
        $dias = collect(range(6, 0))->map(function ($dia) {
            return Carbon::today()->subDays($dia)->format('Y-m-d');
        });

        // Grouping points by date
        $pontos = Ponto::select(DB::raw('DATE(registrado_em) as data'), DB::raw('count(*) as total'))
            ->where('registrado_em', '>=', Carbon::today()->subDays(6)->startOfDay())
            ->groupBy('data')
            ->pluck('total', 'data');

        $totais = $dias->map(function ($dia) use ($pontos) {
            return $pontos->get($dia, 0);
        });

        $labels = $dias->map(function ($dia) {
            return Carbon::parse($dia)->format('d/m');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Batidas de Ponto',
                    'data' => $totais->toArray(),
                    'backgroundColor' => '#E42313',
                    'borderColor' => '#E42313',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
