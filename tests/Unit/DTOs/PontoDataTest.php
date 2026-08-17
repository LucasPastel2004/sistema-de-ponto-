<?php

declare(strict_types=1);

use App\DTOs\PontoData;
use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use Carbon\Carbon;

test('it creates PontoData with valid data', function () {
    $date = Carbon::now();
    $dto = new PontoData(
        colaborador_id: 1,
        tipo: TipoPonto::Entrada,
        registrado_em: $date,
        metodo_validacao: MetodoValidacao::Gps,
        latitude: -23.5505,
        longitude: -46.6333,
        observacao: 'Chegada',
        is_manual: false,
    );

    expect($dto->colaborador_id)->toBe(1)
        ->and($dto->tipo)->toBe(TipoPonto::Entrada)
        ->and($dto->registrado_em)->toBe($date)
        ->and($dto->latitude)->toBe(-23.5505);
});

test('it converts PontoData to array', function () {
    $date = Carbon::now();
    $dto = new PontoData(
        colaborador_id: 1,
        tipo: TipoPonto::Entrada,
        registrado_em: $date,
        metodo_validacao: MetodoValidacao::Gps,
    );

    $array = $dto->toArray();

    expect($array)->toBeArray()
        ->and($array['colaborador_id'])->toBe(1)
        ->and($array['tipo'])->toBe(TipoPonto::Entrada->value);
});

test('it creates PontoData with minimum required fields', function () {
    $date = Carbon::now();
    $dto = new PontoData(
        colaborador_id: 2,
        tipo: TipoPonto::Saida,
        registrado_em: $date,
        metodo_validacao: MetodoValidacao::Biometria,
    );

    expect($dto->latitude)->toBeNull()
        ->and($dto->observacao)->toBeNull()
        ->and($dto->is_manual)->toBeFalse();
});
