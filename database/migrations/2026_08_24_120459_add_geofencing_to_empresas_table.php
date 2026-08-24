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
        Schema::table('empresas', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('email');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('raio_ponto_metros')->default(20)->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'raio_ponto_metros']);
        });
    }
};
