<?php

declare(strict_types=1);

namespace App\DTOs;

use Carbon\Carbon;

readonly class ColaboradorData
{
    public function __construct(
        public int $user_id,
        public int $empresa_id,
        public string $nome,
        public string $cpf,
        public string $matricula,
        public Carbon $data_admissao,
        public bool $ativo = true,
        public ?int $departamento_id = null,
        public ?int $escala_id = null,
        public ?string $cargo = null,
        public ?Carbon $data_demissao = null,
    ) {}

    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'empresa_id' => $this->empresa_id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'matricula' => $this->matricula,
            'data_admissao' => $this->data_admissao->format('Y-m-d'),
            'ativo' => $this->ativo,
            'departamento_id' => $this->departamento_id,
            'escala_id' => $this->escala_id,
            'cargo' => $this->cargo,
            'data_demissao' => $this->data_demissao?->format('Y-m-d'),
        ];
    }
}
