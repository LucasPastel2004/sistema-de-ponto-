<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\EscalaTrabalho;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

function setupApiTest($testCase)
{
    $testCase->empresa = Empresa::create(['razao_social' => 'Empresa Teste', 'nome_fantasia' => 'Teste', 'cnpj' => '11111111111111']);
    $testCase->departamento = Departamento::create(['empresa_id' => $testCase->empresa->id, 'nome' => 'TI']);
    $testCase->escala = EscalaTrabalho::create([
        'nome' => 'Comercial',
        'tipo' => 'fixa',
        'carga_horaria_diaria' => 480,
        'tolerancia_minutos' => 10,
        'intervalo_minutos' => 60,
        'dias_trabalho' => [1, 2, 3, 4, 5],
    ]);
    $testCase->user = User::factory()->create();
    $testCase->colaborador = Colaborador::create([
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

    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Permission::firstOrCreate(['name' => 'aprovar-justificativa']);
    Permission::firstOrCreate(['name' => 'gerenciar-pontos']);
}
