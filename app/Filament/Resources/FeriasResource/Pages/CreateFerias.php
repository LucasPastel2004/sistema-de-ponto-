<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeriasResource\Pages;

use App\Filament\Resources\FeriasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFerias extends CreateRecord
{
    protected static string $resource = FeriasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Se is_coletiva for false, garante que empresa_id seja nulo
        // Se is_coletiva for true, garante que colaborador_id seja nulo
        if (isset($data['is_coletiva']) && $data['is_coletiva']) {
            $data['colaborador_id'] = null;
        } else {
            $data['empresa_id'] = null;
        }
        
        unset($data['is_coletiva']); // Campo virtual não existe na tabela

        $data['aprovado_por'] = auth()->id();
        $data['aprovado_em'] = now();

        return $data;
    }
}
