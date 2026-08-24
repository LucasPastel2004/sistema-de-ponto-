<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MetodoValidacao;
use App\Enums\StatusJustificativa;
use App\Enums\TipoPonto;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\Justificativa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'aprovar-justificativa']);
        Permission::firstOrCreate(['name' => 'gerenciar-pontos']);
        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo(Permission::all());
    }

    private function createEmpresa(): Empresa
    {
        return Empresa::create([
            'razao_social' => 'Empresa Teste',
            'nome_fantasia' => 'Teste',
            'cnpj' => '12345678000199',
            'ativa' => true,
        ]);
    }

    private function createColaborador(User $user, Empresa $empresa): Colaborador
    {
        return Colaborador::create([
            'user_id' => $user->id,
            'empresa_id' => $empresa->id,
            'nome' => $user->name,
            'cpf' => (string) random_int(10000000000, 99999999999),
            'matricula' => 'MAT-'.random_int(1000, 9999),
            'cargo' => 'Analista',
            'data_admissao' => now()->toDateString(),
            'ativo' => true,
        ]);
    }

    public function test_usuario_comum_nao_pode_bater_ponto_para_outro_colaborador(): void
    {
        $empresa = $this->createEmpresa();

        $user1 = User::factory()->create();
        $colab1 = $this->createColaborador($user1, $empresa);

        $user2 = User::factory()->create();
        $colab2 = $this->createColaborador($user2, $empresa);

        $this->actingAs($user1);

        $response = $this->postJson('/api/v1/pontos', [
            'colaborador_id' => $colab2->id,
            'tipo' => TipoPonto::Entrada->value,
            'registrado_em' => now()->toIso8601String(),
            'metodo_validacao' => MetodoValidacao::Gps->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_usuario_sem_permissao_nao_pode_aprovar_justificativa(): void
    {
        $empresa = $this->createEmpresa();

        $user1 = User::factory()->create();
        $colab1 = $this->createColaborador($user1, $empresa);

        $user2 = User::factory()->create();
        $colab2 = $this->createColaborador($user2, $empresa);

        $justificativa = Justificativa::create([
            'colaborador_id' => $colab2->id,
            'data_referencia' => now()->toDateString(),
            'tipo' => 'Falta',
            'descricao' => 'Motivo',
            'status' => StatusJustificativa::Pendente->value,
        ]);

        $this->actingAs($user1);

        $response = $this->patchJson("/api/v1/justificativas/{$justificativa->id}/aprovar", [
            'status' => StatusJustificativa::Aprovada->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_gestor_pode_aprovar_justificativa_da_sua_empresa(): void
    {
        $empresa = $this->createEmpresa();

        $gestorUser = User::factory()->create();
        $gestorUser->givePermissionTo('aprovar-justificativa');
        $gestorColab = $this->createColaborador($gestorUser, $empresa);

        $user2 = User::factory()->create();
        $colab2 = $this->createColaborador($user2, $empresa);

        $justificativa = Justificativa::create([
            'colaborador_id' => $colab2->id,
            'data_referencia' => now()->toDateString(),
            'tipo' => 'Falta',
            'descricao' => 'Motivo',
            'status' => StatusJustificativa::Pendente->value,
        ]);

        $this->actingAs($gestorUser);

        $response = $this->patchJson("/api/v1/justificativas/{$justificativa->id}/aprovar", [
            'status' => StatusJustificativa::Aprovada->value,
        ]);

        $response->assertStatus(200);
    }
}
