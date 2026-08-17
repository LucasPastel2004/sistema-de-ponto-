<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores')->cascadeOnDelete();
            $table->foreignId('ponto_id')->nullable()->constrained('pontos')->nullOnDelete();
            $table->date('data_referencia');
            $table->string('tipo', 100);
            $table->text('descricao');
            $table->string('comprovante_path')->nullable();
            $table->string('status')->default('pendente');
            $table->foreignId('aprovador_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprovado_em')->nullable();
            $table->text('observacao_aprovador')->nullable();
            $table->timestamps();

            $table->index(['colaborador_id', 'data_referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativas');
    }
};
