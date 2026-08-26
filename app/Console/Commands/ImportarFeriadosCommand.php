<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Feriado;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportarFeriadosCommand extends Command
{
    protected $signature = 'feriados:importar {ano? : Ano para importar (padrão: ano atual)}';
    protected $description = 'Importa os feriados nacionais do ano via BrasilAPI e insere na tabela feriados.';

    public function handle(): int
    {
        $ano = $this->argument('ano') ?? now()->year;

        $this->info("Importando feriados nacionais de {$ano} via BrasilAPI...");

        try {
            $response = Http::timeout(15)
                ->get("https://brasilapi.com.br/api/feriados/v1/{$ano}");

            if ($response->failed()) {
                $this->error("Erro ao consultar a API. Status: {$response->status()}");
                return self::FAILURE;
            }

            $feriados = $response->json();

            if (empty($feriados)) {
                $this->warn("Nenhum feriado retornado para o ano {$ano}.");
                return self::SUCCESS;
            }

            $importados = 0;
            $existentes = 0;

            foreach ($feriados as $feriado) {
                $resultado = Feriado::updateOrCreate(
                    [
                        'data' => $feriado['date'],
                        'empresa_id' => null,
                        'nome' => $feriado['name'],
                    ],
                    [
                        'tipo' => 'nacional',
                        'recorrente' => false,
                    ]
                );

                if ($resultado->wasRecentlyCreated) {
                    $importados++;
                    $this->line("  + {$feriado['name']} ({$feriado['date']})");
                } else {
                    $existentes++;
                }
            }

            $this->info("Concluído! {$importados} novos feriados importados, {$existentes} já existiam.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Falha na importação: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
