<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColaboradorResource;
use App\Interfaces\ColaboradorRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ColaboradorController extends Controller
{
    public function __construct(
        private readonly ColaboradorRepositoryInterface $colaboradorRepository
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', \App\Models\Colaborador::class);

        $query = \App\Models\Colaborador::query()->ativos();
        $user = $request->user();

        if (!$user->hasRole('admin') && !$user->hasPermissionTo('gerenciar-pontos') && !$user->hasPermissionTo('aprovar-justificativa')) {
            $colabId = $user->colaborador?->id ?? -1;
            $query->where('id', $colabId);
        } elseif ($user->colaborador) {
            $query->where('empresa_id', $user->colaborador->empresa_id);
        }

        $colaboradores = \Spatie\QueryBuilder\QueryBuilder::for($query)
            ->allowedFilters(['nome', 'matricula', 'departamento_id'])
            ->paginate(15);

        return ColaboradorResource::collection($colaboradores);
    }

    public function show(int $id): ColaboradorResource
    {
        $colaborador = $this->colaboradorRepository->buscarPorId($id);

        abort_if(!$colaborador, 404, 'Colaborador não encontrado.');
        
        $this->authorize('view', $colaborador);

        $colaborador->loadCount('pontos');

        return new ColaboradorResource($colaborador);
    }
}
