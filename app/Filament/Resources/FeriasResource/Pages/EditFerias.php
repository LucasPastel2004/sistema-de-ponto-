<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeriasResource\Pages;

use App\Filament\Resources\FeriasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFerias extends EditRecord
{
    protected static string $resource = FeriasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Define o campo virtual is_coletiva para o form baseando no registro
        $data['is_coletiva'] = is_null($data['colaborador_id']) && !is_null($data['empresa_id']);
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['is_coletiva']) && $data['is_coletiva']) {
            $data['colaborador_id'] = null;
        } else {
            $data['empresa_id'] = null;
        }
        
        unset($data['is_coletiva']);

        return $data;
    }
}
