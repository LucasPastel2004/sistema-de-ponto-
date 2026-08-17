<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ColaboradorController;
use App\Http\Controllers\Api\JustificativaController;
use App\Http\Controllers\Api\PontoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Pontos
    Route::apiResource('pontos', PontoController::class)->only(['index', 'store', 'show']);
    Route::get('pontos/espelho', [PontoController::class, 'espelho']);
    
    // Justificativas
    Route::apiResource('justificativas', JustificativaController::class)->only(['index', 'store', 'show']);
    Route::patch('justificativas/{id}/aprovar', [JustificativaController::class, 'aprovar']);
    Route::patch('justificativas/{id}/rejeitar', [JustificativaController::class, 'rejeitar']);
    
    // Colaboradores
    Route::apiResource('colaboradores', ColaboradorController::class)->only(['index', 'show']);
});
