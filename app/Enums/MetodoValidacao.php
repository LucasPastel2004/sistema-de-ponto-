<?php

declare(strict_types=1);

namespace App\Enums;

enum MetodoValidacao: string
{
    case Gps = 'gps';
    case Wifi = 'wifi';
    case Biometria = 'biometria';
    case Manual = 'manual';

    public function label(): string
    {
        return match($this) {
            self::Gps => 'GPS',
            self::Wifi => 'Wi-Fi',
            self::Biometria => 'Biometria',
            self::Manual => 'Manual',
        };
    }
}
