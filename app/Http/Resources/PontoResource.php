<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PontoResource extends JsonResource
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
            'registrado_em' => $this->registrado_em->format('Y-m-d H:i:s'),
            'metodo_validacao' => $this->metodo_validacao,
            'observacao' => $this->observacao,
            'is_manual' => $this->is_manual,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'colaborador' => new ColaboradorResource($this->whenLoaded('colaborador')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
