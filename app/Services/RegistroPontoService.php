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
        $ultimoPonto = $this->pontoRepository->buscarPontosHoje($data->colaborador_id)->last();
        if ($ultimoPonto && $ultimoPonto->registrado_em->diffInSeconds($data->registrado_em) < 60) {
            throw new InvalidArgumentException('Registro de ponto duplicado.');
        }

        if ($data->latitude !== null && $data->longitude !== null) {
            if (! $this->validarGeolocalizacao($data->colaborador_id, $data->latitude, $data->longitude)) {
                throw new InvalidArgumentException('Você está muito distante da empresa para registrar o ponto.');
            }
        } elseif ($this->exigeGeolocalizacao($data->colaborador_id)) {
            throw new InvalidArgumentException('Localização (GPS) é obrigatória para bater o ponto.');
        }

        if (! $this->validarHorario($data->colaborador_id, $data->tipo)) {
            throw new InvalidArgumentException('Você está fora do horário permitido (incluindo tolerância) para registrar o ponto.');
        }

        return $this->pontoRepository->registrar($data);
    }

    private function exigeGeolocalizacao(int $colaboradorId): bool
    {
        $colaborador = Colaborador::with('empresa')->find($colaboradorId);
        if ($colaborador && $colaborador->empresa->latitude && $colaborador->empresa->longitude) {
            return true;
        }

        return false;
    }

    public function validarGeolocalizacao(int $colaboradorId, float $lat, float $lng): bool
    {
        $colaborador = Colaborador::with('empresa')->find($colaboradorId);
        if (! $colaborador || ! $colaborador->empresa) {
            return true; // Se não tem empresa vinculada, ignora
        }

        $empresa = $colaborador->empresa;
        if (! $empresa->latitude || ! $empresa->longitude) {
            return true; // Se a empresa não configurou GPS, permite em qualquer lugar
        }

        $distancia = $this->calcularDistanciaHaversine(
            (float) $empresa->latitude,
            (float) $empresa->longitude,
            $lat,
            $lng
        );

        $raio = $empresa->raio_ponto_metros ?? 20;

        return $distancia <= $raio;
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
        
        // Se não tem empresa ou a empresa não exige bloqueio, permite.
        if (! $colaborador || ! $colaborador->empresa || ! $colaborador->empresa->bloqueia_ponto_fora_horario) {
            return true;
        }

        // Se a empresa bloqueia, mas o funcionário não tem escala associada, não podemos bloquear.
        if (! $colaborador->escala) {
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
