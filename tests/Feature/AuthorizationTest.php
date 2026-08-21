<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\Justificativa;
use App\Models\Ponto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'aprovar-justificativa']);
        Permission::firstOrCreate(['name' => 'gerenciar-pontos']);
        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo(Permission::all());
    }

    public function test_usuario_comum_nao_pode_bater_ponto_para_outro_colaborador(): void
    {
        $empresa = Empresa::factory()->create();
        
        $user1 = User::factory()->create();
        $colab1 = Colaborador::factory()->create(['user_id' => $user1->id, 'empresa_id' => $empresa->id]);
        
        $user2 = User::factory()->create();
        $colab2 = Colaborador::factory()->create(['user_id' => $user2->id, 'empresa_id' => $empresa->id]);

        $this->actingAs($user1);

        $response = $this->postJson('/api/v1/pontos', [
            'colaborador_id' => $colab2->id,
            'tipo' => TipoPonto::Entrada->value,
            'registrado_em' => now()->toIso8601String(),
            'metodo_validacao' => MetodoValidacao::Geolocalizacao->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_usuario_sem_permissao_nao_pode_aprovar_justificativa(): void
    {
        $empresa = Empresa::factory()->create();
        
        $user1 = User::factory()->create();
        $colab1 = Colaborador::factory()->create(['user_id' => $user1->id, 'empresa_id' => $empresa->id]);
        
        $user2 = User::factory()->create();
        $colab2 = Colaborador::factory()->create(['user_id' => $user2->id, 'empresa_id' => $empresa->id]);

        $justificativa = Justificativa::factory()->create([
            'colaborador_id' => $colab2->id,
            'status' => \App\Enums\StatusJustificativa::Pendente->value
        ]);

        $this->actingAs($user1);

        $response = $this->patchJson("/api/v1/justificativas/{$justificativa->id}/aprovar", [
            'status' => \App\Enums\StatusJustificativa::Aprovada->value,
        ]);

        $response->assertStatus(403);
    }

    public function test_gestor_pode_aprovar_justificativa_da_sua_empresa(): void
    {
        $empresa = Empresa::factory()->create();
        
        $gestorUser = User::factory()->create();
        $gestorUser->givePermissionTo('aprovar-justificativa');
        $gestorColab = Colaborador::factory()->create(['user_id' => $gestorUser->id, 'empresa_id' => $empresa->id]);
        
        $user2 = User::factory()->create();
        $colab2 = Colaborador::factory()->create(['user_id' => $user2->id, 'empresa_id' => $empresa->id]);

        $justificativa = Justificativa::factory()->create([
            'colaborador_id' => $colab2->id,
            'status' => \App\Enums\StatusJustificativa::Pendente->value
        ]);

        $this->actingAs($gestorUser);

        $response = $this->patchJson("/api/v1/justificativas/{$justificativa->id}/aprovar", [
            'status' => \App\Enums\StatusJustificativa::Aprovada->value,
        ]);

        $response->assertStatus(200);
    }
}
