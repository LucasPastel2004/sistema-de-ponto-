<?php

declare(strict_types=1);

use App\Enums\StatusJustificativa;
use App\Enums\TipoPonto;
use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\EscalaTrabalho;
use App\Models\Justificativa;
use App\Models\Ponto;
use App\Models\User;
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
    $this->user = User::factory()->create();
    $this->colaborador = Colaborador::create([
        'user_id' => $this->user->id,
        'empresa_id' => $this->empresa->id,
        'departamento_id' => $this->departamento->id,
        'escala_id' => $this->escala->id,
        'nome' => 'João',
        'cpf' => '12345678901',
        'matricula' => '12345',
        'cargo' => 'Dev',
        'data_admissao' => now(),
    ]);

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'aprovar-justificativa']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gerenciar-pontos']);
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
