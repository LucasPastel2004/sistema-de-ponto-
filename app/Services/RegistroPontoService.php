<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\PontoData;
use App\Enums\TipoPonto;
use App\Interfaces\PontoRepositoryInterface;
use App\Models\Colaborador;
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
        $ultimoPonto = $this->pontoRepository->buscarPontosHoje($data->colaboradorId)->last();
        if ($ultimoPonto && $ultimoPonto->registrado_em->diffInSeconds($data->registradoEm) < 60) {
            throw new InvalidArgumentException('Registro de ponto duplicado.');
        }

        // Carrega colaborador e empresa UMA ÚNICA VEZ para evitar N+1 queries
        // nas validações subsequentes de geolocalização e horário.
        $colaborador = Colaborador::with(['empresa', 'escala'])->find($data->colaboradorId);

        if ($data->latitude !== null && $data->longitude !== null) {
            if (! $this->validarGeolocalizacaoComColaborador($colaborador, $data->latitude, $data->longitude)) {
                throw new InvalidArgumentException('Você está muito distante da empresa para registrar o ponto.');
            }
        } elseif ($this->exigeGeolocalizacaoComColaborador($colaborador)) {
            throw new InvalidArgumentException('Localização (GPS) é obrigatória para bater o ponto.');
        }

        if (! $this->validarHorarioComColaborador($colaborador, $data->tipo)) {
            throw new InvalidArgumentException('Você está fora do horário permitido (incluindo tolerância) para registrar o ponto.');
        }

        return $this->pontoRepository->registrar($data);
    }

    private function exigeGeolocalizacaoComColaborador(?Colaborador $colaborador): bool
    {
        if (! $colaborador || ! $colaborador->empresa) {
            return false;
        }

        if ($colaborador->empresa->latitude && $colaborador->empresa->longitude) {
            return true;
        }

        if (! empty($colaborador->empresa->polos)) {
            return true;
        }

        return false;
    }


    public function validarGeolocalizacao(int $colaboradorId, float $lat, float $lng): bool
    {
        $colaborador = Colaborador::with('empresa')->find($colaboradorId);

        return $this->validarGeolocalizacaoComColaborador($colaborador, $lat, $lng);
    }

    private function validarGeolocalizacaoComColaborador(?Colaborador $colaborador, float $lat, float $lng): bool
    {
        if (! $colaborador || ! $colaborador->empresa) {
            return true; // Se não tem empresa vinculada, ignora
        }

        $empresa = $colaborador->empresa;
        $polos = $empresa->polos ?? [];

        // Adiciona a matriz/local legado à lista de polos a checar
        if ($empresa->latitude && $empresa->longitude) {
            $polos[] = [
                'latitude' => $empresa->latitude,
                'longitude' => $empresa->longitude,
                'raio_ponto_metros' => $empresa->raio_ponto_metros ?? 20,
            ];
        }

        if (empty($polos)) {
            return true; // Se a empresa não configurou GPS em nenhum lugar, permite em qualquer lugar
        }

        foreach ($polos as $polo) {
            if (! isset($polo['latitude']) || ! isset($polo['longitude'])) {
                continue;
            }

            $distancia = $this->calcularDistanciaHaversine(
                (float) $polo['latitude'],
                (float) $polo['longitude'],
                $lat,
                $lng
            );

            $raio = $polo['raio_ponto_metros'] ?? 20;

            if ($distancia <= $raio) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcula a distância em metros entre dois pontos (Haversine)
     */
    private function calcularDistanciaHaversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Raio da Terra em metros

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function validarHorario(int $colaboradorId, TipoPonto $tipo): bool
    {
        $colaborador = Colaborador::with(['empresa', 'escala'])->find($colaboradorId);

        return $this->validarHorarioComColaborador($colaborador, $tipo);
    }

    private function validarHorarioComColaborador(?Colaborador $colaborador, TipoPonto $tipo): bool
    {
        if (! $colaborador || ! $colaborador->empresa || ! $colaborador->empresa->bloqueia_ponto_fora_horario || ! $colaborador->escala || $tipo === TipoPonto::IntervaloInicio || $tipo === TipoPonto::IntervaloFim) {
            return true;
        }

        $escala = $colaborador->escala;
        $agora = now();
        $horaEsperada = null;

        if ($tipo === TipoPonto::Entrada && $escala->horario_entrada) {
            $horaEsperada = \Carbon\Carbon::createFromFormat('H:i:s', $escala->horario_entrada);
        } elseif ($tipo === TipoPonto::Saida && $escala->horario_saida) {
            $horaEsperada = \Carbon\Carbon::createFromFormat('H:i:s', $escala->horario_saida);
        }

        // Se não tem hora esperada definida na escala para este tipo de ponto, permite.
        if (! $horaEsperada) {
            return true;
        }

        // Ajusta a hora esperada para hoje, mantendo a hora/minuto configurada.
        $horaEsperada->setDate($agora->year, $agora->month, $agora->day);

        // Diferença absoluta em minutos
        $diferencaMinutos = $agora->diffInMinutes($horaEsperada);
        $tolerancia = $escala->tolerancia_minutos ?? 10;

        return $diferencaMinutos <= $tolerancia;
    }
}

