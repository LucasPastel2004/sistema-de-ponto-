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

    public function gerarResumoMensal(\App\Models\Colaborador $colaborador, Collection $pontos, int $mes, int $ano): array
    {
        $diasTrabalhados = 0;
        $totalHorasTrabalhadas = 0.0;
        $totalAtrasosMinutos = 0;
        $totalHorasExtras = 0.0;
        $faltas = 0;
        
        $pontosPorDia = $pontos->groupBy(fn ($p) => $p->registrado_em instanceof Carbon ? $p->registrado_em->format('Y-m-d') : Carbon::parse($p->registrado_em)->format('Y-m-d'));
        
        $escala = $colaborador->escala;
        $cargaDiariaHoras = $escala ? (float) $escala->carga_horaria_diaria : 8.0;

        foreach ($pontosPorDia as $data => $pontosDoDia) {
            $diasTrabalhados++;
            $horasDia = $this->calcularHorasTrabalhadas($pontosDoDia);
            $totalHorasTrabalhadas += $horasDia;

            if ($escala) {
                $saldo = $horasDia - $cargaDiariaHoras;
                if ($saldo > 0) {
                    $totalHorasExtras += $saldo;
                } elseif ($saldo < 0) {
                    $totalAtrasosMinutos += (int) abs($saldo * 60);
                }
            }
        }

        // Falta simples: assume dias de semana como trabalho e subtrai os dias que tem ponto. 
        // Em um sistema real precisaria subtrair feriados e fds conforme a escala
        $diasUteisEsperados = 22; // média mensal
        if ($escala && is_array($escala->dias_trabalho)) {
            $diasDaSemanaTrabalhados = count($escala->dias_trabalho);
            $diasUteisEsperados = (int) (($diasDaSemanaTrabalhados / 7) * 30); 
        }

        if ($diasTrabalhados < $diasUteisEsperados) {
            $faltas = $diasUteisEsperados - $diasTrabalhados;
        }

        return [
            'total_horas_trabalhadas' => round($totalHorasTrabalhadas, 2),
            'total_horas_extras' => round($totalHorasExtras, 2),
            'total_atrasos_minutos' => $totalAtrasosMinutos,
            'dias_trabalhados' => $diasTrabalhados,
            'faltas' => $faltas,
        ];
    }
}
