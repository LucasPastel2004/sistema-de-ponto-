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

    /**
     * Retorna o total de minutos de intervalo do dia.
     * Alias para calcularHorasIntervalo() * 60, em minutos.
     */
    public function calcularIntervalo(Collection $pontos): float
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

        foreach ($pontosPorDia as $pontosDoDia) {
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

        // Calcula dias úteis esperados com base no mês/ano real e nos dias de trabalho da escala, excluindo férias e feriados.
        $diasUteisEsperados = $this->calcularDiasUteisEsperados($mes, $ano, $escala?->dias_trabalho, $colaborador);

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

    /**
     * Calcula os dias úteis esperados para o mês/ano baseado nos dias de trabalho da escala.
     * Desconta os feriados (Nacionais/Empresa) e férias do colaborador.
     *
     * @param array<int>|null $diasTrabalho Array com números do dia da semana (0=Dom, 1=Seg..., 6=Sáb)
     */
    private function calcularDiasUteisEsperados(int $mes, int $ano, ?array $diasTrabalho = null, ?\App\Models\Colaborador $colaborador = null): int
    {
        // Dias de trabalho padrão: segunda (1) a sexta (5)
        $diasDaSemana = (is_array($diasTrabalho) && !empty($diasTrabalho))
            ? $diasTrabalho
            : [1, 2, 3, 4, 5];

        $inicio = Carbon::create($ano, $mes, 1)->startOfDay();
        $fim = $inicio->copy()->endOfMonth();

        $diasUteis = 0;
        $dia = $inicio->copy();
        
        // Cachear feriados do mês para performance
        $feriadosDoMes = [];
        if ($colaborador) {
            $feriadosDoMes = \App\Models\Feriado::whereMonth('data', $mes)
                ->whereYear('data', $ano)
                ->where(function ($query) use ($colaborador) {
                    $query->whereNull('empresa_id')
                          ->orWhere('empresa_id', $colaborador->empresa_id);
                })->pluck('data')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();
        }

        while ($dia->lte($fim)) {
            $isDiaTrabalho = in_array($dia->dayOfWeek, $diasDaSemana, true);

            if ($isDiaTrabalho && $colaborador) {
                // Checa se é feriado
                if (in_array($dia->toDateString(), $feriadosDoMes)) {
                    $isDiaTrabalho = false;
                }
                
                // Checa se está de férias
                if ($isDiaTrabalho && \App\Models\Ferias::colaboradorEmFerias($colaborador->id, $dia, $colaborador->empresa_id)) {
                    $isDiaTrabalho = false;
                }
            }

            if ($isDiaTrabalho) {
                $diasUteis++;
            }
            $dia->addDay();
        }

        return $diasUteis;
    }
}
