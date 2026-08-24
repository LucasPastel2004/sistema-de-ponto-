<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoPonto: string
{
    case Entrada = 'entrada';
    case Saida = 'saida';
    case IntervaloInicio = 'intervalo_inicio';
    case IntervaloFim = 'intervalo_fim';

    public function label(): string
    {
        return match ($this) {
            self::Entrada => 'Entrada',
            self::Saida => 'Saída',
            self::IntervaloInicio => 'Início do Intervalo',
            self::IntervaloFim => 'Fim do Intervalo',
        };
    }
}
