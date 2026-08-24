<?php

declare(strict_types=1);

use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use App\Models\Ponto;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    setupApiTest($this);
});

test('it requires authentication to access pontos', function () {
    $response = $this->getJson('/api/v1/pontos');
    $response->assertStatus(401);
});

test('it registers a new ponto successfully', function () {
    Sanctum::actingAs($this->colaborador->user);

    $response = $this->postJson('/api/v1/pontos', [
        'colaborador_id' => $this->colaborador->id,
        'tipo' => TipoPonto::Entrada->value,
        'registrado_em' => Carbon::now()->toIso8601String(),
        'metodo_validacao' => MetodoValidacao::Wifi->value,
        'latitude' => -23.123,
        'longitude' => -46.123,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'tipo', 'registrado_em']]);

    $this->assertDatabaseHas('pontos', [
        'colaborador_id' => $this->colaborador->id,
        'tipo' => TipoPonto::Entrada->value,
    ]);
});

test('it validates required fields on store', function () {
    Sanctum::actingAs($this->colaborador->user);

    $response = $this->postJson('/api/v1/pontos', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['tipo', 'registrado_em', 'metodo_validacao']);
});

test('it lists pontos for authenticated user', function () {
    Sanctum::actingAs($this->colaborador->user);

    Ponto::create([
        'colaborador_id' => $this->colaborador->id,
        'tipo' => TipoPonto::Entrada,
        'registrado_em' => now(),
        'metodo_validacao' => MetodoValidacao::Gps,
    ]);

    $response = $this->getJson('/api/v1/pontos');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});
