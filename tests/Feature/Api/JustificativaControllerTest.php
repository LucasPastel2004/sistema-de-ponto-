<?php

declare(strict_types=1);

use App\Enums\StatusJustificativa;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    setupApiTest($this);
});

test('it creates justificativa', function () {
    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/justificativas', [
        'colaborador_id' => $this->colaborador->id,
        'data_referencia' => '2023-10-10',
        'tipo' => 'Esquecimento',
        'descricao' => 'Esqueci de bater o ponto',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('justificativas', [
        'colaborador_id' => $this->colaborador->id,
        'tipo' => 'Esquecimento',
        'status' => StatusJustificativa::Pendente->value,
    ]);
});

test('it validates required fields', function () {
    Sanctum::actingAs($this->user);

    $response = $this->postJson('/api/v1/justificativas', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['data_referencia', 'tipo', 'descricao']);
});
