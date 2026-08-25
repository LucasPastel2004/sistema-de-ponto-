<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use App\Http\Requests\StorePontoRequest;
use Carbon\Carbon;

readonly class PontoData
{
    public function __construct(
        public int $colaboradorId,
        public TipoPonto $tipo,
        public Carbon $registradoEm,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $ipAddress = null,
        public ?array $deviceInfo = null,
        public MetodoValidacao $metodoValidacao = MetodoValidacao::Manual,
        public ?string $observacao = null,
        public bool $isManual = false,
    ) {}

    public static function fromRequest(StorePontoRequest $request): self
    {
        return new self(
            colaborador_id: (int) $request->validated('colaborador_id'),
            tipo: TipoPonto::from($request->validated('tipo')),
            registrado_em: Carbon::parse($request->validated('registrado_em')),
            latitude: $request->has('latitude') ? (float) $request->validated('latitude') : null,
            longitude: $request->has('longitude') ? (float) $request->validated('longitude') : null,
            ip_address: $request->ip(),
            device_info: [
                'user_agent' => $request->userAgent(),
            ],
            metodo_validacao: MetodoValidacao::from($request->validated('metodo_validacao')),
            observacao: $request->validated('observacao'),
            is_manual: true,
        );
    }

    public function toArray(): array
    {
        return [
            'colaborador_id' => $this->colaborador_id,
            'tipo' => $this->tipo->value,
            'registrado_em' => $this->registrado_em->format('Y-m-d H:i:s'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'ip_address' => $this->ip_address,
            'device_info' => $this->device_info,
            'metodo_validacao' => $this->metodo_validacao->value,
            'observacao' => $this->observacao,
            'is_manual' => $this->is_manual,
        ];
    }
}
