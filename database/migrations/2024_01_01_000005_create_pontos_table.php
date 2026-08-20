<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pontos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colaborador_id')->constrained('colaboradores')->cascadeOnDelete();
            $table->string('tipo');
            $table->timestampTz('registrado_em');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->jsonb('device_info')->nullable();
            $table->string('metodo_validacao');
            $table->text('observacao')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->foreignId('aprovado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['colaborador_id', 'registrado_em']);
            $table->index('registrado_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pontos');
    }
};
