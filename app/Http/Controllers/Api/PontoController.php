<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\PontoData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePontoRequest;
use App\Http\Resources\EspelhoPontoResource;
use App\Http\Resources\PontoResource;
use App\Models\Ponto;
use App\Services\EspelhoPontoService;
use App\Services\RegistroPontoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PontoController extends Controller
{
    public function __construct(
        private readonly RegistroPontoService $registroPontoService,
        private readonly EspelhoPontoService $espelhoPontoService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $pontos = QueryBuilder::for(Ponto::class)
            ->allowedFilters([
                AllowedFilter::exact('colaborador_id'),
                AllowedFilter::exact('tipo'),
                AllowedFilter::callback('data_inicio', function ($query, $value) {
                    $query->whereDate('registrado_em', '>=', $value);
                }),
                AllowedFilter::callback('data_fim', function ($query, $value) {
                    $query->whereDate('registrado_em', '<=', $value);
                }),
            ])
            ->allowedSorts(['registrado_em'])
            ->defaultSort('-registrado_em')
            ->paginate(15);

        return PontoResource::collection($pontos);
    }

    public function store(StorePontoRequest $request): JsonResponse
    {
        $data = PontoData::fromRequest($request);

        $ponto = $this->registroPontoService->registrar($data);

        return response()->json(new PontoResource($ponto), 201);
    }

    public function show(int $id): PontoResource
    {
        $ponto = Ponto::findOrFail($id);

        return new PontoResource($ponto);
    }

    public function espelho(Request $request): EspelhoPontoResource
    {
        $validated = $request->validate([
            'colaborador_id' => ['required', 'exists:colaboradores,id'],
            'mes' => ['required', 'integer', 'between:1,12'],
            'ano' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
        ]);

        $dados = $this->espelhoPontoService->gerar(
            (int) $validated['colaborador_id'],
            (int) $validated['mes'],
            (int) $validated['ano']
        );

        return new EspelhoPontoResource($dados);
    }
}
