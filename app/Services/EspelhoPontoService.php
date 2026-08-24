<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\PontoRepositoryInterface;

class EspelhoPontoService
{
    public function __construct(
        private readonly PontoRepositoryInterface $pontoRepository,
        private readonly CalculoJornadaService $calculoJornadaService
    ) {}

    public function gerar(int $colaboradorId, int $mes, int $ano): array
    {
        $pontos = $this->pontoRepository->espelhoPonto($colaboradorId, $mes, $ano);

        $resumo = $this->calculoJornadaService->gerarResumoMensal($colaboradorId, $mes, $ano);

        return [
            'colaborador_id' => $colaboradorId,
            'mes' => $mes,
            'ano' => $ano,
            'registros' => $pontos->groupBy(fn ($ponto) => $ponto->registrado_em->format('Y-m-d')),
            'resumo' => $resumo,
        ];
    }

    public function gerarPdf(int $colaboradorId, int $mes, int $ano): string
    {
        $dados = $this->gerar($colaboradorId, $mes, $ano);

        // TODO: Implement PDF generation
        return "storage/app/public/espelhos/{$colaboradorId}_{$ano}_{$mes}.pdf";
    }
}
