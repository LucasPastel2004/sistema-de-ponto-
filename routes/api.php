<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ColaboradorController;
use App\Http\Controllers\Api\JustificativaController;
use App\Http\Controllers\Api\PontoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rota padrão /api/user para autenticação Sanctum
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:api'])->group(function () {
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

