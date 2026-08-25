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
        $colaborador = \App\Models\Colaborador::with('escala')->findOrFail($colaboradorId);

        $resumo = $this->calculoJornadaService->gerarResumoMensal($colaborador, $pontos, $mes, $ano);

        return [
            'colaborador' => $colaborador,
            'mes' => $mes,
            'ano' => $ano,
            'registros' => $pontos->groupBy(fn ($ponto) => $ponto->registrado_em->format('Y-m-d')),
            'resumo' => $resumo,
        ];
    }

    public function gerarPdf(int $colaboradorId, int $mes, int $ano): string
    {
        $dados = $this->gerar($colaboradorId, $mes, $ano);

        if (! view()->exists('pdf.espelho-ponto')) {
            throw new \\InvalidArgumentException('A template de PDF do espelho de ponto não foi encontrada. Contate o suporte técnico.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.espelho-ponto', $dados);

        $fileName = "espelhos/{$colaboradorId}_{$ano}_{$mes}.pdf";
        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $pdf->output());

        return "storage/{$fileName}";
    }
}
