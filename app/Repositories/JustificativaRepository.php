<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\JustificativaData;
use App\Enums\StatusJustificativa;
use App\Interfaces\JustificativaRepositoryInterface;
use App\Models\Justificativa;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class JustificativaRepository implements JustificativaRepositoryInterface
{
    public function criar(JustificativaData $data): Justificativa
    {
        return Justificativa::create($data->toArray());
    }

    public function buscarPorId(int $id): ?Justificativa
    {
        return Justificativa::find($id);
    }

    public function listarPendentes(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Justificativa::class)
            ->where('status', StatusJustificativa::Pendente->value)
            ->allowedFilters(['colaborador_id', 'tipo', 'data_referencia'])
            ->allowedSorts(['data_referencia', 'created_at'])
            ->paginate(15);
    }

    public function aprovar(int $id, int $aprovadorId, ?string $observacao = null): Justificativa
    {
        return DB::transaction(function () use ($id, $aprovadorId, $observacao) {
            $justificativa = Justificativa::findOrFail($id);
            $justificativa->update([
                'status' => StatusJustificativa::Aprovada->value,
                'aprovador_id' => $aprovadorId,
                'aprovado_em' => now(),
                'observacao_aprovador' => $observacao,
            ]);

            return $justificativa;
        });
    }

    public function rejeitar(int $id, int $aprovadorId, ?string $observacao = null): Justificativa
    {
        return DB::transaction(function () use ($id, $aprovadorId, $observacao) {
            $justificativa = Justificativa::findOrFail($id);
            $justificativa->update([
                'status' => StatusJustificativa::Rejeitada->value,
                'aprovador_id' => $aprovadorId,
                'aprovado_em' => now(),
                'observacao_aprovador' => $observacao,
            ]);

            return $justificativa;
        });
    }
}
