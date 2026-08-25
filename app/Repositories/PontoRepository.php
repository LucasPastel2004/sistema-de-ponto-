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
        $ponto = Ponto::create($data->toArray());

        // Invalida o cache do espelho do mês atual para o colaborador,
        // garantindo que novos pontos apareçam imediatamente no espelho.
        $mes = $data->registrado_em->month;
        $ano = $data->registrado_em->year;
        Cache::forget("espelho_ponto_{$data->colaborador_id}_{$mes}_{$ano}");

        return $ponto;
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
            $data = Carbon::create($ano, $mes, 1);

            return Ponto::where('colaborador_id', $colaboradorId)
                ->whereBetween('registrado_em', [$data->copy()->startOfMonth(), $data->copy()->endOfMonth()])
                ->orderBy('registrado_em')
                ->get();
        });
    }

    public function buscarPontosHoje(int $colaboradorId): Collection
    {
        $hoje = now();

        return Ponto::where('colaborador_id', $colaboradorId)
            ->whereBetween('registrado_em', [$hoje->copy()->startOfDay(), $hoje->copy()->endOfDay()])
            ->orderBy('registrado_em')
            ->get();
    }
}
