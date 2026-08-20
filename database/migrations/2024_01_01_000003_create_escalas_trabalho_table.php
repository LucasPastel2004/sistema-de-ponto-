<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalas_trabalho', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo');
            $table->integer('carga_horaria_diaria');
            $table->integer('tolerancia_minutos')->default(10);
            $table->time('horario_entrada')->nullable();
            $table->time('horario_saida')->nullable();
            $table->integer('intervalo_minutos')->default(60);
            $table->jsonb('dias_trabalho')->nullable();
            $table->boolean('ativa')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalas_trabalho');
    }
};
