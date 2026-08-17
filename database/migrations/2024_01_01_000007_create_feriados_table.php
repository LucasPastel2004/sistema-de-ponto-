<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feriados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->date('data');
            $table->string('tipo');
            $table->boolean('recorrente')->default(false);
            $table->timestamps();

            $table->unique(['empresa_id', 'data', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feriados');
    }
};
