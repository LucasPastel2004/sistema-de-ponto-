<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JustificativaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'data_referencia' => $this->data_referencia->format('Y-m-d'),
            'status' => [
                'valor' => $this->status,
                'label' => $this->status, // Poderia usar um método do enum para label em pt-br
                'color' => $this->getStatusColor(),
            ],
            'comprovante_url' => $this->comprovante_path ? asset('storage/'.$this->comprovante_path) : null,
            'observacao_aprovador' => $this->observacao_aprovador,
            'aprovado_em' => $this->aprovado_em?->format('Y-m-d H:i:s'),
            'colaborador' => new ColaboradorResource($this->whenLoaded('colaborador')),
            'ponto' => new PontoResource($this->whenLoaded('ponto')),
        ];
    }

    private function getStatusColor(): string
    {
        return match ($this->status) {
            'Pendente' => 'warning',
            'Aprovada' => 'success',
            'Rejeitada' => 'danger',
            default => 'secondary',
        };
    }
}
