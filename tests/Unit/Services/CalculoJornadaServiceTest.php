<?php

declare(strict_types=1);

use App\Enums\TipoPonto;
use App\Models\Ponto;
use App\Services\CalculoJornadaService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

test('it calculates worked hours for a simple day', function () {
    $pontos = new Collection([
        new Ponto(['tipo' => TipoPonto::Entrada, 'registrado_em' => Carbon::parse('08:00:00')]),
        new Ponto(['tipo' => TipoPonto::IntervaloInicio, 'registrado_em' => Carbon::parse('12:00:00')]),
        new Ponto(['tipo' => TipoPonto::IntervaloFim, 'registrado_em' => Carbon::parse('13:00:00')]),
        new Ponto(['tipo' => TipoPonto::Saida, 'registrado_em' => Carbon::parse('17:00:00')]),
    ]);

    $service = new CalculoJornadaService();
    $horas = $service->calcularHorasTrabalhadas($pontos);

    // 08:00 to 12:00 = 4h
    // 13:00 to 17:00 = 4h
    // Total = 8h
    expect($horas)->toBe(8.0);
});

test('it calculates interval duration', function () {
    $pontos = new Collection([
        new Ponto(['tipo' => TipoPonto::IntervaloInicio, 'registrado_em' => Carbon::parse('12:00:00')]),
        new Ponto(['tipo' => TipoPonto::IntervaloFim, 'registrado_em' => Carbon::parse('13:30:00')]),
    ]);

    $service = new CalculoJornadaService();
    $intervalo = $service->calcularHorasIntervalo($pontos);

    expect($intervalo)->toBe(1.5);
});

test('it returns zero hours for empty collection', function () {
    $service = new CalculoJornadaService();
    $horas = $service->calcularHorasTrabalhadas(new Collection());

    expect($horas)->toBe(0.0);
});
