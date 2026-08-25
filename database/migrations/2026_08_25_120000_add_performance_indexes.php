<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('justificativas', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('colaboradores', function (Blueprint $table) {
            $table->index('ativo');
            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::table('justificativas', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropIndex(['ativo']);
            $table->dropIndex(['empresa_id', 'ativo']);
        });
    }
};
