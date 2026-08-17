<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;

class CalculoJornadaService
{
    public function calcularHorasTrabalhadas(Collection $pontos): array
    {
        // TODO: Implementar lógica complexa de cálculo baseada na escala do colaborador
        // Exemplo simplificado de retorno
        return [
            'horas_normais' => 8.0,
            'horas_extras' => 0.0,
            'horas_faltantes' => 0.0,
            'atraso_minutos' => 0,
        ];
    }

    public function calcularIntervalo(Collection $pontos): ?float
    {
        // TODO: Retornar a duração do intervalo de descanso em minutos
        return 60.0;
    }

    public function gerarResumoMensal(int $colaboradorId, int $mes, int $ano): array
    {
        // TODO: Implementar agregação mensal
        return [
            'total_horas_trabalhadas' => 160.0,
            'total_horas_extras' => 10.0,
            'total_atrasos_minutos' => 15,
            'dias_trabalhados' => 20,
            'faltas' => 1,
        ];
    }
}
