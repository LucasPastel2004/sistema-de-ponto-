<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\PontoData;
use App\Enums\TipoPonto;
use App\Interfaces\PontoRepositoryInterface;
use App\Models\Ponto;
use InvalidArgumentException;

class RegistroPontoService
{
    public function __construct(
        private readonly PontoRepositoryInterface $pontoRepository
    ) {}

    public function registrar(PontoData $data): Ponto
    {
        // Valida se já existe ponto no último minuto para evitar duplicidade
        $ultimoPonto = $this->pontoRepository->buscarPontosHoje($data->colaborador_id)->last();
        if ($ultimoPonto && $ultimoPonto->registrado_em->diffInSeconds($data->registrado_em) < 60) {
            throw new InvalidArgumentException('Registro de ponto duplicado.');
        }

        if ($data->latitude !== null && $data->longitude !== null) {
            if (!$this->validarGeolocalizacao($data->latitude, $data->longitude)) {
                throw new InvalidArgumentException('Localização inválida para registro de ponto.');
            }
        }

        if (!$this->validarHorario($data->colaborador_id, $data->tipo)) {
            // Em um caso real poderia registrar como atraso ou hora extra
        }

        return $this->pontoRepository->registrar($data);
    }

    public function validarGeolocalizacao(float $lat, float $lng): bool
    {
        // TODO: Implementar lógica de geofencing com base no endereço da empresa ou locais permitidos
        return true;
    }

    public function validarHorario(int $colaboradorId, TipoPonto $tipo): bool
    {
        // TODO: Buscar escala do colaborador e validar o horário registrado vs o esperado
        return true;
    }
}
