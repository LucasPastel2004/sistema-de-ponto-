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
use Carbon\Carbon;
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
        $this->authorize('viewAny', Ponto::class);

        $query = Ponto::query();
        $user = $request->user();

        if (! $user->hasRole('admin') && ! $user->hasPermissionTo('gerenciar-pontos')) {
            $colabId = $user->colaborador?->id ?? -1;
            $query->where('colaborador_id', $colabId);
        } elseif ($user->colaborador) {
            $query->whereHas('colaborador', function ($q) use ($user) {
                $q->where('empresa_id', $user->colaborador->empresa_id);
            });
        }

        $pontos = QueryBuilder::for($query)
            ->allowedFilters([
                AllowedFilter::exact('colaborador_id'),
                AllowedFilter::exact('tipo'),
                AllowedFilter::callback('data_inicio', function ($query, $value) {
                    $query->where('registrado_em', '>=', Carbon::parse($value)->startOfDay());
                }),
                AllowedFilter::callback('data_fim', function ($query, $value) {
                    $query->where('registrado_em', '<=', Carbon::parse($value)->endOfDay());
                }),
            ])
            ->allowedSorts(['registrado_em'])
            ->defaultSort('-registrado_em')
            ->paginate(15);

        return PontoResource::collection($pontos);
    }

    public function store(StorePontoRequest $request): JsonResponse
    {
        $this->authorize('create', [Ponto::class, (int) $request->colaborador_id]);

        $data = PontoData::fromRequest($request);

        $ponto = $this->registroPontoService->registrar($data);

        return (new PontoResource($ponto))
            ->response()
            ->setStatusCode(201);
    }

    public function show(int $id): PontoResource
    {
        $ponto = Ponto::findOrFail($id);

        $this->authorize('view', $ponto);

        return new PontoResource($ponto);
    }

    public function espelho(Request $request): EspelhoPontoResource
    {
        $validated = $request->validate([
            'colaborador_id' => ['required', 'exists:colaboradores,id'],
            'mes' => ['required', 'integer', 'between:1,12'],
            'ano' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
        ]);

        $this->authorize('create', [Ponto::class, (int) $validated['colaborador_id']]);

        $dados = $this->espelhoPontoService->gerar(
            (int) $validated['colaborador_id'],
            (int) $validated['mes'],
            (int) $validated['ano']
        );

        return new EspelhoPontoResource($dados);
    }
}
