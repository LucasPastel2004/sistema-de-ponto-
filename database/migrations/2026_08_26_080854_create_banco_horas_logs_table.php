<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('banco_horas_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores')->cascadeOnDelete();
            $table->date('data_referencia');
            $table->integer('minutos_ajuste');
            $table->string('observacao')->nullable();
            $table->timestamps();

            // Garantir que cada dia tenha apenas 1 log automático do sistema (ou facilita a substituição)
            $table->unique(['colaborador_id', 'data_referencia']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banco_horas_logs');
    }
};
