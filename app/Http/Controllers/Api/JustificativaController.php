<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\JustificativaData;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJustificativaRequest;
use App\Http\Requests\UpdateJustificativaRequest;
use App\Http\Resources\JustificativaResource;
use App\Interfaces\JustificativaRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JustificativaController extends Controller
{
    public function __construct(
        private readonly JustificativaRepositoryInterface $justificativaRepository
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $justificativas = $this->justificativaRepository->listarPendentes();

        return JustificativaResource::collection($justificativas);
    }

    public function store(StoreJustificativaRequest $request): JsonResponse
    {
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

        abort_if(!$justificativa, 404, 'Justificativa não encontrada.');

        return new JustificativaResource($justificativa);
    }

    public function aprovar(UpdateJustificativaRequest $request, int $id): JustificativaResource
    {
        $justificativa = $this->justificativaRepository->aprovar(
            $id,
            $request->user()->id,
            $request->validated('observacao_aprovador')
        );

        return new JustificativaResource($justificativa);
    }

    public function rejeitar(UpdateJustificativaRequest $request, int $id): JustificativaResource
    {
        $justificativa = $this->justificativaRepository->rejeitar(
            $id,
            $request->user()->id,
            $request->validated('observacao_aprovador')
        );

        return new JustificativaResource($justificativa);
    }
}
