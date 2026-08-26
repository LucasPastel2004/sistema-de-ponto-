<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeriadoResource\Pages;

use App\Filament\Resources\FeriadoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFeriado extends CreateRecord
{
    protected static string $resource = FeriadoResource::class;
}
