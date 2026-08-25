<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\JustificativaData;
use App\Enums\StatusJustificativa;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJustificativaRequest;
use App\Http\Requests\UpdateJustificativaRequest;
use App\Http\Resources\JustificativaResource;
use App\Interfaces\JustificativaRepositoryInterface;
use App\Models\Justificativa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\QueryBuilder;

class JustificativaController extends Controller
{
    public function __construct(
        private readonly JustificativaRepositoryInterface $justificativaRepository
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Justificativa::class);

        $query = Justificativa::query();
        $user = $request->user();

        if (! $user->hasRole('admin') && ! $user->hasPermissionTo('gerenciar-pontos') && ! $user->hasPermissionTo('aprovar-justificativa')) {
            $colabId = $user->colaborador?->id ?? -1;
            $query->where('colaborador_id', $colabId);
        } elseif ($user->colaborador) {
            $query->whereHas('colaborador', function ($q) use ($user) {
                $q->where('empresa_id', $user->colaborador->empresa_id);
            });
        }

        $justificativas = QueryBuilder::for($query)
            ->allowedFilters(['colaborador_id', 'tipo', 'data_referencia', 'status'])
            ->allowedSorts(['data_referencia', 'created_at'])
            ->defaultSort('-created_at')
            ->paginate(15);

        return JustificativaResource::collection($justificativas);
    }

    public function store(StoreJustificativaRequest $request): JsonResponse
    {
        $this->authorize('create', [Justificativa::class, (int) $request->colaborador_id]);

        $path = null;
        if ($request->hasFile('comprovante')) {
            $path = $request->file('comprovante')->store('comprovantes', 'public');
        }

        $data = JustificativaData::fromRequest($request, $path);

        $justificativa = $this->justificativaRepository->criar($data);

        return response()->json(new JustificativaResource($justificativa), 201);
    }

    public function show(int $id): JustificativaResource
    {
        $justificativa = $this->justificativaRepository->buscarPorId($id);

        abort_if(! $justificativa, 404, 'Justificativa não encontrada.');

        $this->authorize('view', $justificativa);

        return new JustificativaResource($justificativa);
    }

    public function aprovar(UpdateJustificativaRequest $request, int $id): JustificativaResource
    {
        $justificativa = $this->justificativaRepository->buscarPorId($id);
        abort_if(! $justificativa, 404, 'Justificativa não encontrada.');

        $this->authorize('aprovar', $justificativa);

        $justificativa = $this->justificativaRepository->aprovar(
            $id,
            $request->user()->id,
            $request->validated('observacao_aprovador')
        );

        return new JustificativaResource($justificativa);
    }

    public function rejeitar(UpdateJustificativaRequest $request, int $id): JustificativaResource
    {
        $justificativa = $this->justificativaRepository->buscarPorId($id);
        abort_if(! $justificativa, 404, 'Justificativa não encontrada.');

        $this->authorize('rejeitar', $justificativa);

        $justificativa = $this->justificativaRepository->rejeitar(
            $id,
            $request->user()->id,
            $request->validated('observacao_aprovador')
        );

        return new JustificativaResource($justificativa);
    }
}
