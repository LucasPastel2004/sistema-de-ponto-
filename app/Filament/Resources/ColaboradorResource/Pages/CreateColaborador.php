<?php

declare(strict_types=1);

namespace App\Filament\Resources\ColaboradorResource\Pages;

use App\Filament\Resources\ColaboradorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateColaborador extends CreateRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'name' => $data['nome'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $data['user_id'] = $user->id;

        unset($data['username'], $data['email'], $data['password']);

        return $data;
    }
}
