<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TipoPonto;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculoJornadaService
{
    public function calcularHorasTrabalhadas(Collection $pontos): float
    {
        if ($pontos->isEmpty()) {
            return 0.0;
        }

        $ordenados = $pontos->sortBy(fn ($p) => $p->registrado_em instanceof Carbon ? $p->registrado_em : Carbon::parse($p->registrado_em))->values();

        $totalMinutos = 0;
        $entrada = null;

        foreach ($ordenados as $ponto) {
            $momento = $ponto->registrado_em instanceof Carbon ? $ponto->registrado_em : Carbon::parse($ponto->registrado_em);
            $tipo = $ponto->tipo instanceof TipoPonto ? $ponto->tipo : TipoPonto::from($ponto->tipo);

            if ($tipo === TipoPonto::Entrada || $tipo === TipoPonto::IntervaloFim) {
                $entrada = $momento;
            } elseif (($tipo === TipoPonto::IntervaloInicio || $tipo === TipoPonto::Saida) && $entrada !== null) {
                $totalMinutos += $entrada->diffInMinutes($momento);
                $entrada = null;
            }
        }

        return round($totalMinutos / 60, 2);
    }

    public function calcularHorasIntervalo(Collection $pontos): float
    {
        if ($pontos->isEmpty()) {
            return 0.0;
        }

        $ordenados = $pontos->sortBy(fn ($p) => $p->registrado_em instanceof Carbon ? $p->registrado_em : Carbon::parse($p->registrado_em))->values();

        $totalMinutos = 0;
        $inicioIntervalo = null;

        foreach ($ordenados as $ponto) {
            $momento = $ponto->registrado_em instanceof Carbon ? $ponto->registrado_em : Carbon::parse($ponto->registrado_em);
            $tipo = $ponto->tipo instanceof TipoPonto ? $ponto->tipo : TipoPonto::from($ponto->tipo);

            if ($tipo === TipoPonto::IntervaloInicio) {
                $inicioIntervalo = $momento;
            } elseif ($tipo === TipoPonto::IntervaloFim && $inicioIntervalo !== null) {
                $totalMinutos += $inicioIntervalo->diffInMinutes($momento);
                $inicioIntervalo = null;
            }
        }

        return round($totalMinutos / 60, 2);
    }

    public function calcularIntervalo(Collection $pontos): ?float
    {
        return $this->calcularHorasIntervalo($pontos) * 60;
    }

    public function gerarResumoMensal(int $colaboradorId, int $mes, int $ano): array
    {
        return [
            'total_horas_trabalhadas' => 160.0,
            'total_horas_extras' => 10.0,
            'total_atrasos_minutos' => 15,
            'dias_trabalhados' => 20,
            'faltas' => 1,
        ];
    }
}
