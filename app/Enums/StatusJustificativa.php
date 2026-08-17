<?php

declare(strict_types=1);

namespace App\Enums;

enum StatusJustificativa: string
{
    case Pendente = 'pendente';
    case Aprovada = 'aprovada';
    case Rejeitada = 'rejeitada';

    public function label(): string
    {
        return match($this) {
            self::Pendente => 'Pendente',
            self::Aprovada => 'Aprovada',
            self::Rejeitada => 'Rejeitada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pendente => 'warning',
            self::Aprovada => 'success',
            self::Rejeitada => 'danger',
        };
    }
}
