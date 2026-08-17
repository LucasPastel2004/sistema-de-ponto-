<?php

declare(strict_types=1);

namespace App\Filament\Resources\PontoResource\Pages;

use App\Filament\Resources\PontoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPonto extends EditRecord
{
    protected static string $resource = PontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
