<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Colaborador;
use App\Models\Ponto;
use App\Enums\TipoPonto;
use App\Enums\MetodoValidacao;
use Carbon\Carbon;

class CalcularBancoHorasCommand extends Command
{
    protected $signature = 'ponto:fechamento-diario {data? : Data no formato YYYY-MM-DD (padrão é ontem)} {--colaborador= : ID do colaborador específico}';
    protected $description = 'Realiza o fechamento diário dos pontos, calcula horas extras/faltas e atualiza o banco de horas.';

    public function handle()
    {
        $dataArg = $this->argument('data');
        $dataAlvo = $dataArg ? Carbon::parse($dataArg) : Carbon::yesterday();
        $colaboradorId = $this->option('colaborador');

        $this->info("Iniciando fechamento para a data: " . $dataAlvo->format('Y-m-d'));

        $query = Colaborador::with('escala')->where('ativo', true);
        if ($colaboradorId) {
            $query->where('id', $colaboradorId);
        }
        
        $colaboradores = $query->get();

        foreach ($colaboradores as $colaborador) {
            if (!$colaborador->escala) continue;
            
            $isDiaTrabalho = true;
            if (is_array($colaborador->escala->dias_trabalho)) {
                $isDiaTrabalho = in_array($dataAlvo->dayOfWeek, $colaborador->escala->dias_trabalho);
            }

            // Verifica se está de Férias
            if ($isDiaTrabalho) {
                if (\App\Models\Ferias::colaboradorEmFerias($colaborador->id, $dataAlvo, $colaborador->empresa_id)) {
                    $isDiaTrabalho = false;
                    $this->info("{$colaborador->nome} está de férias hoje.");
                }
            }

            // Verifica Feriado Nacional ou da Empresa
            if ($isDiaTrabalho) {
                $isFeriado = \App\Models\Feriado::whereDate('data', $dataAlvo)
                    ->where(function ($query) use ($colaborador) {
                        $query->whereNull('empresa_id')
                              ->orWhere('empresa_id', $colaborador->empresa_id);
                    })->exists();

                if ($isFeriado) {
                    $isDiaTrabalho = false;
                    $this->info("Hoje é feriado para {$colaborador->nome}.");
                }
            }

            $pontos = Ponto::where('colaborador_id', $colaborador->id)
                ->whereDate('registrado_em', $dataAlvo)
                ->orderBy('registrado_em')
                ->get();
            
            $trabalhouHoje = $pontos->count() > 0;

            if (!$isDiaTrabalho && !$trabalhouHoje) {
                continue;
            }

            if ($trabalhouHoje) {
                $ultimoPonto = $pontos->last();
                if (in_array($ultimoPonto->tipo, [TipoPonto::Entrada, TipoPonto::IntervaloFim])) {
                    $fechamento = $dataAlvo->copy()->endOfDay()->setSecond(0); // 23:59:00
                    $novoPonto = Ponto::create([
                        'colaborador_id' => $colaborador->id,
                        'registrado_em' => $fechamento,
                        'tipo' => TipoPonto::Saida,
                        'is_manual' => true,
                        'metodo_validacao' => MetodoValidacao::Manual,
                        'observacao' => 'Fechamento automático (Esquecimento)',
                    ]);
                    $pontos->push($novoPonto);
                    $this->warn("Fechamento automático inserido para {$colaborador->nome}");
                }
            }

            $minutosTrabalhados = 0;
            $entradaTracker = null;

            foreach ($pontos as $p) {
                if ($p->tipo === TipoPonto::Entrada || $p->tipo === TipoPonto::IntervaloFim) {
                    $entradaTracker = Carbon::parse($p->registrado_em);
                } elseif (($p->tipo === TipoPonto::Saida || $p->tipo === TipoPonto::IntervaloInicio) && $entradaTracker) {
                    $saidaTracker = Carbon::parse($p->registrado_em);
                    $minutosTrabalhados += $entradaTracker->diffInMinutes($saidaTracker);
                    $entradaTracker = null;
                }
            }

            $cargaDiaria = $isDiaTrabalho ? (int) $colaborador->escala->carga_horaria_diaria : 0;
            $novoDiff = $minutosTrabalhados - $cargaDiaria;

            $logExistente = \App\Models\BancoHorasLog::where('colaborador_id', $colaborador->id)
                ->whereDate('data_referencia', $dataAlvo)
                ->first();

            $diffAnterior = $logExistente ? $logExistente->minutos_ajuste : 0;
            
            $ajusteNoSaldoTotal = $novoDiff - $diffAnterior;

            if ($ajusteNoSaldoTotal !== 0) {
                $colaborador->saldo_horas += $ajusteNoSaldoTotal;
                $colaborador->save();
            }

            if ($logExistente) {
                if ($novoDiff === 0) {
                    $logExistente->delete();
                } elseif ($novoDiff !== $diffAnterior) {
                    $logExistente->update([
                        'minutos_ajuste' => $novoDiff,
                        'observacao' => 'Reprocessado/Atualizado',
                    ]);
                }
            } elseif ($novoDiff !== 0) {
                \App\Models\BancoHorasLog::create([
                    'colaborador_id' => $colaborador->id,
                    'data_referencia' => $dataAlvo,
                    'minutos_ajuste' => $novoDiff,
                    'observacao' => 'Fechamento diário',
                ]);
            }
            
            $this->info("{$colaborador->nome}: " . ($ajusteNoSaldoTotal >= 0 ? '+' : '') . "{$ajusteNoSaldoTotal} mins de ajuste (Diff do Dia: {$novoDiff}). Saldo Atual: {$colaborador->saldo_horas}");
        }
        
        $this->info("Fechamento diário concluído.");
    }
}
