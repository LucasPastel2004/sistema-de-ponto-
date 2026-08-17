<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ColaboradorResource extends JsonResource
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
            'nome' => $this->nome,
            'cpf' => $this->maskCpf($this->cpf),
            'matricula' => $this->matricula,
            'cargo' => $this->cargo,
            'data_admissao' => $this->data_admissao->format('Y-m-d'),
            'ativo' => $this->ativo,
            'departamento_id' => $this->departamento_id,
            'empresa_id' => $this->empresa_id,
            'pontos' => PontoResource::collection($this->whenLoaded('pontos')),
        ];
    }

    private function maskCpf(string $cpf): string
    {
        $cleanCpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cleanCpf) !== 11) {
            return '***.***.***-**';
        }

        return '***.***.***-' . substr($cleanCpf, -2);
    }
}
