<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\StatusJustificativa;
use App\Http\Requests\StoreJustificativaRequest;
use Carbon\Carbon;

readonly class JustificativaData
{
    public function __construct(
        public int $colaboradorId,
        public Carbon $dataReferencia,
        public string $tipo,
        public string $descricao,
        public ?int $pontoId = null,
        public ?string $comprovantePath = null,
        public StatusJustificativa $status = StatusJustificativa::Pendente,
    ) {}

    public static function fromRequest(StoreJustificativaRequest $request, ?string $comprovantePath = null): self
    {
        return new self(
            colaborador_id: (int) $request->validated('colaborador_id'),
            data_referencia: Carbon::parse($request->validated('data_referencia')),
            tipo: $request->validated('tipo'),
            descricao: $request->validated('descricao'),
            ponto_id: $request->has('ponto_id') ? (int) $request->validated('ponto_id') : null,
            comprovante_path: $comprovantePath,
            status: StatusJustificativa::Pendente,
        );
    }

    public function toArray(): array
    {
        return [
            'colaborador_id' => $this->colaborador_id,
            'data_referencia' => $this->data_referencia->format('Y-m-d'),
            'tipo' => $this->tipo,
            'descricao' => $this->descricao,
            'ponto_id' => $this->ponto_id,
            'comprovante_path' => $this->comprovante_path,
            'status' => $this->status->value,
        ];
    }
}
