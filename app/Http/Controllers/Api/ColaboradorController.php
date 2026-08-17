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
        $colaboradores = $this->colaboradorRepository->listarAtivos();

        return ColaboradorResource::collection($colaboradores);
    }

    public function show(int $id): ColaboradorResource
    {
        $colaborador = $this->colaboradorRepository->buscarPorId($id);

        abort_if(!$colaborador, 404, 'Colaborador não encontrado.');

        $colaborador->loadCount('pontos');

        return new ColaboradorResource($colaborador);
    }
}
