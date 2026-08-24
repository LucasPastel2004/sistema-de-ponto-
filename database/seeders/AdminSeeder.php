<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Cria a role "admin" e atribui ao usuário informado.
     */
    public function run(): void
    {
        // Garante que a role admin existe
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $usuarios = User::all(['id', 'name', 'email']);

        if ($usuarios->isEmpty()) {
            $this->command->warn('Nenhum usuário encontrado. Crie um primeiro com:');
            $this->command->warn('docker compose exec -it app php artisan make:filament-user');

            return;
        }

        // Mostra os usuários disponíveis
        $this->command->table(['ID', 'Nome', 'Email'], $usuarios->toArray());

        $email = $this->command->ask('Digite o e-mail do usuário que receberá a role admin');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->command->error("Usuário com e-mail '{$email}' não encontrado.");

            return;
        }

        $user->assignRole('admin');

        $this->command->info("✓ Role 'admin' atribuída ao usuário: {$user->name} ({$user->email})");
    }
}
