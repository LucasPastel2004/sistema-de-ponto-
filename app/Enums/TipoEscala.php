<?php

declare(strict_types=1);

namespace App\Enums;

enum TipoEscala: string
{
    case Fixa = 'fixa';
    case Flexivel = 'flexivel';
    case Revezamento = 'revezamento';
    case Doze36 = '12x36';

    public function label(): string
    {
        return match ($this) {
            self::Fixa => 'Fixa',
            self::Flexivel => 'Flexível',
            self::Revezamento => 'Revezamento',
            self::Doze36 => '12x36',
        };
    }
}
