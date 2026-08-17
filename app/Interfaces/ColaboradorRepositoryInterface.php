<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\DTOs\ColaboradorData;
use App\Models\Colaborador;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

interface ColaboradorRepositoryInterface
{
    public function buscarPorId(int $id): ?Colaborador;

    public function buscarPorMatricula(string $matricula): ?Colaborador;

    public function listarAtivos(array $filters = []): LengthAwarePaginator;

    public function buscarComPontos(int $colaboradorId, Carbon $inicio, Carbon $fim): ?Colaborador;

    public function criar(ColaboradorData $data): Colaborador;

    public function atualizar(int $id, ColaboradorData $data): Colaborador;
}
