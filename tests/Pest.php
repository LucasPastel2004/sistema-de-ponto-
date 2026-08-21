<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

function setupApiTest($testCase)
{
    $testCase->empresa = \App\Models\Empresa::create(['razao_social' => 'Empresa Teste', 'nome_fantasia' => 'Teste', 'cnpj' => '11111111111111']);
    $testCase->departamento = \App\Models\Departamento::create(['empresa_id' => $testCase->empresa->id, 'nome' => 'TI']);
    $testCase->escala = \App\Models\EscalaTrabalho::create([
        'nome' => 'Comercial',
        'tipo' => 'fixa',
        'carga_horaria_diaria' => 480,
        'tolerancia_minutos' => 10,
        'intervalo_minutos' => 60,
        'dias_trabalho' => [1, 2, 3, 4, 5],
    ]);
    $testCase->user = \App\Models\User::factory()->create();
    $testCase->colaborador = \App\Models\Colaborador::create([
        'user_id' => $testCase->user->id,
        'empresa_id' => $testCase->empresa->id,
        'departamento_id' => $testCase->departamento->id,
        'escala_id' => $testCase->escala->id,
        'nome' => 'Joǜo',
        'cpf' => '12345678901',
        'matricula' => '12345',
        'cargo' => 'Dev',
        'data_admissao' => now(),
    ]);

    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'aprovar-justificativa']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gerenciar-pontos']);
}
