<?php

declare(strict_types=1);

namespace App\Filament\Resources\JustificativaResource\Pages;

use App\Filament\Resources\JustificativaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJustificativa extends EditRecord
{
    protected static string $resource = JustificativaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
