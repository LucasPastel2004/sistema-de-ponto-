<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\PontoData;
use App\Interfaces\PontoRepositoryInterface;
use App\Models\Ponto;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PontoRepository implements PontoRepositoryInterface
{
    public function registrar(PontoData $data): Ponto
    {
        return Ponto::create($data->toArray());
    }

    public function buscarPorId(int $id): ?Ponto
    {
        return Ponto::find($id);
    }

    public function buscarPorColaborador(int $colaboradorId, ?Carbon $inicio = null, ?Carbon $fim = null): Collection
    {
        $query = Ponto::where('colaborador_id', $colaboradorId);

        if ($inicio && $fim) {
            $query->whereBetween('registrado_em', [$inicio, $fim]);
        }

        return $query->orderBy('registrado_em')->get();
    }

    public function buscarPorPeriodo(Carbon $inicio, Carbon $fim): Collection
    {
        return Ponto::whereBetween('registrado_em', [$inicio, $fim])
            ->orderBy('registrado_em')
            ->get();
    }

    public function espelhoPonto(int $colaboradorId, int $mes, int $ano): Collection
    {
        $cacheKey = "espelho_ponto_{$colaboradorId}_{$mes}_{$ano}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($colaboradorId, $mes, $ano) {
            return Ponto::where('colaborador_id', $colaboradorId)
                ->whereMonth('registrado_em', $mes)
                ->whereYear('registrado_em', $ano)
                ->orderBy('registrado_em')
                ->get();
        });
    }

    public function buscarPontosHoje(int $colaboradorId): Collection
    {
        return Ponto::where('colaborador_id', $colaboradorId)
            ->whereDate('registrado_em', now()->toDateString())
            ->orderBy('registrado_em')
            ->get();
    }
}
