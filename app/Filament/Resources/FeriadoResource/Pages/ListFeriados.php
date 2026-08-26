<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeriadoResource\Pages;

use App\Filament\Resources\FeriadoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeriados extends ListRecords
{
    protected static string $resource = FeriadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
