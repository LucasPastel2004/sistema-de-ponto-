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
        return [
            'colaborador_id' => $this->resource['colaborador_id'],
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
