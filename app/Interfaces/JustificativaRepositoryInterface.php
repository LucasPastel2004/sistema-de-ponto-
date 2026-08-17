<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\DTOs\JustificativaData;
use App\Models\Justificativa;
use Illuminate\Pagination\LengthAwarePaginator;

interface JustificativaRepositoryInterface
{
    public function criar(JustificativaData $data): Justificativa;

    public function buscarPorId(int $id): ?Justificativa;

    public function listarPendentes(array $filters = []): LengthAwarePaginator;

    public function aprovar(int $id, int $aprovadorId, ?string $observacao = null): Justificativa;

    public function rejeitar(int $id, int $aprovadorId, ?string $observacao = null): Justificativa;
}
