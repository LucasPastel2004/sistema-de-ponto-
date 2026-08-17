<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\ColaboradorData;
use App\Interfaces\ColaboradorRepositoryInterface;
use App\Models\Colaborador;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ColaboradorRepository implements ColaboradorRepositoryInterface
{
    public function buscarPorId(int $id): ?Colaborador
    {
        return Colaborador::find($id);
    }

    public function buscarPorMatricula(string $matricula): ?Colaborador
    {
        return Colaborador::where('matricula', $matricula)->first();
    }

    public function listarAtivos(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Colaborador::class)
            ->where('ativo', true)
            ->allowedFilters([
                'nome',
                'matricula',
                AllowedFilter::exact('departamento_id'),
                AllowedFilter::exact('empresa_id'),
            ])
            ->allowedSorts([
                'nome',
                'matricula',
                'data_admissao',
            ])
            ->paginate(15);
    }

    public function buscarComPontos(int $colaboradorId, Carbon $inicio, Carbon $fim): ?Colaborador
    {
        return Colaborador::with(['pontos' => function ($query) use ($inicio, $fim) {
            $query->whereBetween('registrado_em', [$inicio, $fim]);
        }])->find($colaboradorId);
    }

    public function criar(ColaboradorData $data): Colaborador
    {
        return Colaborador::create($data->toArray());
    }

    public function atualizar(int $id, ColaboradorData $data): Colaborador
    {
        $colaborador = Colaborador::findOrFail($id);
        $colaborador->update($data->toArray());

        return $colaborador;
    }
}
