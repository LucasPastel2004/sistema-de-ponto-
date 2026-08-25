<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EspelhoPontoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\Colaborador $colaborador */
        $colaborador = $this->resource['colaborador'];

        return [
            'colaborador_id' => $colaborador->id,
            'colaborador_nome' => $colaborador->nome,
            'colaborador_matricula' => $colaborador->matricula,
            'mes' => $this->resource['mes'],
            'ano' => $this->resource['ano'],
            'resumo' => $this->resource['resumo'],
            'registros_diarios' => $this->formatRegistros($this->resource['registros']),
        ];
    }

    private function formatRegistros($registrosAgrupados): array
    {
        $formatado = [];
        foreach ($registrosAgrupados as $data => $pontos) {
            $formatado[] = [
                'data' => $data,
                'pontos' => PontoResource::collection($pontos),
            ];
        }

        return $formatado;
    }
}
