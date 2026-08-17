<?php

declare(strict_types=1);

use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\EscalaTrabalho;
use App\Models\Ponto;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->empresa = Empresa::create(['razao_social' => 'Empresa Teste', 'nome_fantasia' => 'Teste', 'cnpj' => '11111111111111']);
    $this->departamento = Departamento::create(['empresa_id' => $this->empresa->id, 'nome' => 'TI']);
    $this->escala = EscalaTrabalho::create([
        'nome' => 'Comercial',
        'tipo' => 'fixa',
        'carga_horaria_diaria' => 480,
        'tolerancia_minutos' => 10,
        'intervalo_minutos' => 60,
        'dias_trabalho' => [1, 2, 3, 4, 5],
    ]);
    $this->colaborador = Colaborador::create([
        'user_id' => User::factory()->create()->id,
        'empresa_id' => $this->empresa->id,
        'departamento_id' => $this->departamento->id,
        'escala_id' => $this->escala->id,
        'nome' => 'João',
        'cpf' => '12345678901',
        'matricula' => '12345',
        'cargo' => 'Dev',
        'data_admissao' => now(),
    ]);
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
