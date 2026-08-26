<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ferias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->nullable()->constrained('colaboradores')->cascadeOnDelete();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->cascadeOnDelete();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('tipo')->default('integral'); // integral, parcial
            $table->integer('dias_vendidos')->default(0); // abono pecuniário
            $table->text('observacao')->nullable();
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('aprovado_em')->nullable();
            $table->timestamps();

            // Índices para consultas rápidas
            $table->index(['colaborador_id', 'data_inicio', 'data_fim']);
            $table->index(['empresa_id', 'data_inicio', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ferias');
    }
};
