<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Empresa;
use App\Models\Ponto;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExportacaoAfdService
{
    /**
     * Gera o conteúdo do Arquivo Fonte de Dados (AFD) no formato estrito (MTE Portaria 671).
     *
     * @param string $dataInicial Formato Y-m-d
     * @param string $dataFinal Formato Y-m-d
     * @param Empresa|null $empresa Empresa filtrada (ou primeira ativa se null)
     * @return string Conteúdo posicional TXT
     */
    public function gerarAfd(string $dataInicial, string $dataFinal, ?Empresa $empresa = null): string
    {
        $empresa = $empresa ?? Empresa::ativas()->first();

        if (!$empresa) {
            throw new \Exception("Nenhuma empresa cadastrada para exportação.");
        }

        $pontos = Ponto::with('colaborador')
            ->whereHas('colaborador', function ($q) use ($empresa) {
                $q->where('empresa_id', $empresa->id);
            })
            ->whereBetween('registrado_em', [
                Carbon::parse($dataInicial)->startOfDay(),
                Carbon::parse($dataFinal)->endOfDay()
            ])
            ->orderBy('registrado_em', 'asc')
            ->get();

        $linhas = [];
        $nsr = 1;

        // TIPO 1: Cabeçalho
        $linhas[] = $this->gerarCabecalho($nsr, $empresa, $dataInicial, $dataFinal);
        $nsr++;

        // TIPO 3: Detalhe das Marcações de Ponto
        foreach ($pontos as $ponto) {
            $linhas[] = $this->gerarRegistroMarcacao($nsr, $ponto);
            $nsr++;
        }

        // TIPO 9: Trailer (Totalizador)
        $linhas[] = $this->gerarTrailer($nsr, count($linhas) + 1);

        return implode("\r\n", $linhas); // Padrão Windows/DOS
    }

    private function gerarCabecalho(int $nsr, Empresa $empresa, string $dataInicial, string $dataFinal): string
    {
        $tipo = '1';
        $tipoIdentificador = strlen(preg_replace('/\D/', '', $empresa->cnpj)) > 11 ? '1' : '2'; // 1=CNPJ, 2=CPF
        $cnpjCpf = str_pad(preg_replace('/\D/', '', $empresa->cnpj), 14, '0', STR_PAD_LEFT);
        $cei = str_pad('', 12, '0'); // Não utilizamos CEI
        $razaoSocial = str_pad(substr($empresa->razao_social, 0, 150), 150, ' ', STR_PAD_RIGHT);
        $dataInicialFormatada = Carbon::parse($dataInicial)->format('dmY');
        $dataFinalFormatada = Carbon::parse($dataFinal)->format('dmY');
        $dataGeracao = now()->format('dmY');
        $horaGeracao = now()->format('Hi');
        
        return str_pad((string)$nsr, 9, '0', STR_PAD_LEFT)
            . $tipo
            . $tipoIdentificador
            . $cnpjCpf
            . $cei
            . $razaoSocial
            . $dataInicialFormatada
            . $dataFinalFormatada
            . $dataGeracao
            . $horaGeracao;
    }

    private function gerarRegistroMarcacao(int $nsr, Ponto $ponto): string
    {
        $tipo = '3';
        $dataHora = Carbon::parse($ponto->registrado_em);
        $dataStr = $dataHora->format('dmY');
        $horaStr = $dataHora->format('Hi');
        
        // Na falta do PIS (antigamente 11 chars), usamos o CPF
        $cpf = str_pad(preg_replace('/\D/', '', $ponto->colaborador->cpf ?? ''), 11, '0', STR_PAD_LEFT);
        
        return str_pad((string)$nsr, 9, '0', STR_PAD_LEFT)
            . $tipo
            . $dataStr
            . $horaStr
            . $cpf;
    }

    private function gerarTrailer(int $nsr, int $totalRegistros): string
    {
        $tipo = '9';
        // Total de registros tipo 2 (não usamos), tipo 3 (marcações), tipo 4 (eventos) e tipo 5
        // Apenas o trailer necessita informar essas posições mesmo que nulas
        
        return str_pad((string)$nsr, 9, '0', STR_PAD_LEFT)
            . $tipo
            . str_pad((string)($totalRegistros - 2), 9, '0', STR_PAD_LEFT); // Quantidade apenas de marcações
    }
}
