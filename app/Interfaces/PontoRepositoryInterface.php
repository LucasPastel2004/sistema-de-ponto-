<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\DTOs\PontoData;
use App\Models\Ponto;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface PontoRepositoryInterface
{
    public function registrar(PontoData $data): Ponto;

    public function buscarPorId(int $id): ?Ponto;

    public function buscarPorColaborador(int $colaboradorId, ?Carbon $inicio = null, ?Carbon $fim = null): Collection;

    public function buscarPorPeriodo(Carbon $inicio, Carbon $fim): Collection;

    public function espelhoPonto(int $colaboradorId, int $mes, int $ano): Collection;

    public function buscarPontosHoje(int $colaboradorId): Collection;
}
