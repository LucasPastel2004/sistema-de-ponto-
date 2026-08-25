<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Departamento;
use App\Models\EscalaTrabalho;
use App\Models\Colaborador;
use App\Models\Ponto;
use App\Models\Justificativa;
use Faker\Factory as Faker;
use Carbon\Carbon;
use App\Enums\TipoPonto;
use App\Enums\MetodoValidacao;
use App\Enums\StatusJustificativa;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        $admin = User::first();
        
        $this->command->info('Criando Empresas...');
        $empresas = [];
        for ($i = 1; $i <= 2; $i++) {
            $empresas[] = Empresa::create([
                'razao_social' => $faker->company,
                'nome_fantasia' => $faker->companySuffix . ' ' . $faker->word,
                'cnpj' => $faker->cnpj(false),
                'telefone' => $faker->cellphoneNumber,
                'email' => $faker->companyEmail,
                'ativa' => true,
                'polos' => [
                    ['nome' => 'Sede', 'latitude' => -23.550520, 'longitude' => -46.633308, 'raio' => 200],
                    ['nome' => 'Filial', 'latitude' => -22.906847, 'longitude' => -43.172896, 'raio' => 200]
                ]
            ]);
        }
        
        $this->command->info('Criando Departamentos e Escalas...');
        $departamentos = [];
        $escalas = [];
        foreach ($empresas as $empresa) {
            $departamentos[] = Departamento::create([
                
                'empresa_id' => $empresa->id, 'nome' => 'Operacoes ' . $empresa->id,
                
            ]);
            
            $escalas[] = EscalaTrabalho::create([
                
                'nome' => 'Comercial 08h-18h',
                'tipo' => 'fixa',
                'dias_trabalho' => [1,2,3,4,5],
                'horario_entrada' => '08:00',
                'horario_saida' => '18:00',
                'carga_horaria_diaria' => 480
            ]);
        }
        
        $this->command->info('Criando Colaboradores...');
        $colaboradores = [];
        if ($admin && !$admin->colaborador) {
            $colabAdmin = Colaborador::create([
                'user_id' => $admin->id,
                'empresa_id' => $empresas[0]->id,
                'departamento_id' => $departamentos[0]->id,
                'escala_id' => $escalas[0]->id,
                'nome' => $admin->name,
                'cpf' => $faker->cpf(false),
                'matricula' => 'ADM0001',
                'cargo' => 'Administrador Geral',
                'data_admissao' => Carbon::now()->subYears(2)->toDateString(),
                'ativo' => true,
            ]);
            $colaboradores[] = $colabAdmin;
        }

        for ($i = 0; $i < 10; $i++) {
            $empresaIndex = $i % 2;
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            $colab = Colaborador::create([
                'user_id' => $user->id,
                'empresa_id' => $empresas[$empresaIndex]->id,
                'departamento_id' => $departamentos[$empresaIndex]->id,
                'escala_id' => $escalas[$empresaIndex]->id,
                'nome' => $user->name,
                'cpf' => $faker->cpf(false),
                'matricula' => 'MAT' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),
                'cargo' => $faker->jobTitle,
                'data_admissao' => Carbon::now()->subMonths(rand(1, 24))->toDateString(),
                'ativo' => true,
            ]);
            
            $colaboradores[] = $colab;
        }
        
        $this->command->info('Criando Pontos e Justificativas...');
        foreach ($colaboradores as $colab) {
            for ($daysAgo = 10; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);
                if ($date->isWeekend()) continue;
                
                $entrada = $date->copy()->setHour(8)->setMinute(rand(0, 15));
                $intervaloIni = $date->copy()->setHour(12)->setMinute(rand(0, 15));
                $intervaloFim = $date->copy()->setHour(13)->setMinute(rand(0, 15));
                $saida = $date->copy()->setHour(17)->setMinute(rand(0, 30));
                
                $pontoTypes = [
                    ['tipo' => TipoPonto::Entrada, 'time' => $entrada],
                    ['tipo' => TipoPonto::IntervaloInicio, 'time' => $intervaloIni],
                    ['tipo' => TipoPonto::IntervaloFim, 'time' => $intervaloFim],
                    ['tipo' => TipoPonto::Saida, 'time' => $saida],
                ];
                
                $skipIndex = rand(1, 100) > 90 ? rand(0, 3) : -1;
                
                foreach ($pontoTypes as $idx => $pt) {
                    if ($idx === $skipIndex) continue;
                    
                    Ponto::create([
                        'colaborador_id' => $colab->id,
                        'tipo' => $pt['tipo'],
                        'registrado_em' => $pt['time'],
                        'latitude' => -23.550520 + (rand(-10, 10) / 10000),
                        'longitude' => -46.633308 + (rand(-10, 10) / 10000),
                        'ip_address' => $faker->ipv4,
                        'metodo_validacao' => MetodoValidacao::Gps,
                        'is_manual' => false,
                    ]);
                }
                
                if ($skipIndex !== -1 && rand(0, 1)) {
                    $isApproved = rand(0, 1);
                    Justificativa::create([
                        'colaborador_id' => $colab->id,
                        'data_referencia' => $date->toDateString(),
                        'tipo' => 'Esquecimento',
                        'descricao' => 'Esqueci de bater o ponto.',
                        'status' => $isApproved ? StatusJustificativa::Aprovada->value : StatusJustificativa::Pendente->value,
                        'aprovador_id' => $isApproved && $admin ? $admin->id : null,
                        'aprovado_em' => $isApproved ? Carbon::now() : null,
                    ]);
                }
            }
            
            if (rand(1, 100) > 80) {
                 Justificativa::create([
                    'colaborador_id' => $colab->id,
                    'data_referencia' => Carbon::today()->subDays(rand(2, 5))->toDateString(),
                    'tipo' => 'Atestado Medico',
                    'descricao' => 'Consulta medica.',
                    'status' => StatusJustificativa::Aprovada->value,
                    'aprovador_id' => $admin ? $admin->id : null,
                    'aprovado_em' => Carbon::now(),
                 ]);
            }
        }

        $this->command->info('Dados gerados com sucesso!');
    }
}
