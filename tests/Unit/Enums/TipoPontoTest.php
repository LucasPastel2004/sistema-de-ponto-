<?php

declare(strict_types=1);

use App\Enums\TipoPonto;

test('it has correct values', function () {
    expect(TipoPonto::Entrada->value)->toBe('entrada')
        ->and(TipoPonto::IntervaloInicio->value)->toBe('intervalo_inicio')
        ->and(TipoPonto::IntervaloFim->value)->toBe('intervalo_fim')
        ->and(TipoPonto::Saida->value)->toBe('saida');
});

test('it returns labels', function () {
    expect(TipoPonto::Entrada->label())->toBe('Entrada')
        ->and(TipoPonto::Saida->label())->toBe('Saída');
});
