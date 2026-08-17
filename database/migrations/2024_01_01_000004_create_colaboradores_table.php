<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos')->nullOnDelete();
            $table->foreignId('escala_id')->nullable()->constrained('escalas_trabalho')->nullOnDelete();
            $table->string('nome');
            $table->text('cpf');
            $table->string('matricula', 50);
            $table->string('cargo')->nullable();
            $table->date('data_admissao');
            $table->date('data_demissao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'matricula']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaboradores');
    }
};
