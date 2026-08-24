<?php

declare(strict_types=1);

namespace App\Filament\Resources\ColaboradorResource\Pages;

use App\Filament\Resources\ColaboradorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditColaborador extends EditRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;
        if ($user) {
            $data['username'] = $user->username;
            $data['email'] = $user->email;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;
        if ($user) {
            $user->name = $data['nome'];
            $user->username = $data['username'];
            $user->email = $data['email'] ?? null;
            if (! empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }
            $user->save();
        }

        unset($data['username'], $data['email'], $data['password']);

        return $data;
    }
}
