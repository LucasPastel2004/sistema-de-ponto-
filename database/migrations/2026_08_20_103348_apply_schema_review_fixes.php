<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Colaborador;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Converter json -> jsonb e timestamp -> timestamptz
        DB::statement("ALTER TABLE pontos ALTER COLUMN registrado_em TYPE timestamptz USING registrado_em AT TIME ZONE 'UTC'");
        DB::statement("ALTER TABLE pontos ALTER COLUMN device_info TYPE jsonb USING device_info::jsonb");
        DB::statement("ALTER TABLE empresas ALTER COLUMN endereco TYPE jsonb USING endereco::jsonb");
        DB::statement("ALTER TABLE escalas_trabalho ALTER COLUMN dias_trabalho TYPE jsonb USING dias_trabalho::jsonb");
        DB::statement("ALTER TABLE audit_logs ALTER COLUMN old_values TYPE jsonb USING old_values::jsonb");
        DB::statement("ALTER TABLE audit_logs ALTER COLUMN new_values TYPE jsonb USING new_values::jsonb");

        // 2. Tratar problema do unique cpf criptografado criando um hash indexável
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'cpf']);
            $table->string('cpf_hash', 64)->nullable()->after('cpf');
        });

        // 3. Preencher os hashes dos colaboradores existentes
        Colaborador::chunk(100, function ($colaboradores) {
            foreach ($colaboradores as $colaborador) {
                if ($colaborador->cpf) {
                    $colaborador->cpf_hash = hash('sha256', $colaborador->cpf);
                    $colaborador->saveQuietly(); // salva sem disparar eventos
                }
            }
        });

        Schema::table('colaboradores', function (Blueprint $table) {
            // Caso haja CPFs nulos ou duplicados, o unique ignorará nulos no Postgres 
            $table->unique(['empresa_id', 'cpf_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'cpf_hash']);
            $table->dropColumn('cpf_hash');
            $table->unique(['empresa_id', 'cpf']);
        });

        DB::statement("ALTER TABLE pontos ALTER COLUMN registrado_em TYPE timestamp without time zone USING registrado_em::timestamp without time zone");
        DB::statement("ALTER TABLE pontos ALTER COLUMN device_info TYPE json USING device_info::json");
        DB::statement("ALTER TABLE empresas ALTER COLUMN endereco TYPE json USING endereco::json");
        DB::statement("ALTER TABLE escalas_trabalho ALTER COLUMN dias_trabalho TYPE json USING dias_trabalho::json");
        DB::statement("ALTER TABLE audit_logs ALTER COLUMN old_values TYPE json USING old_values::json");
        DB::statement("ALTER TABLE audit_logs ALTER COLUMN new_values TYPE json USING new_values::json");
    }
};
