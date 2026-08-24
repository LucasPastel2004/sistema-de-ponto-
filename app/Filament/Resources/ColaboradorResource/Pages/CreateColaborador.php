<?php

declare(strict_types=1);

namespace App\Filament\Resources\ColaboradorResource\Pages;

use App\Filament\Resources\ColaboradorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateColaborador extends CreateRecord
{
    protected static string $resource = ColaboradorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = \App\Models\User::create([
            'name' => $data['nome'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        ]);
        
        $data['user_id'] = $user->id;
        
        unset($data['username'], $data['email'], $data['password']);
        
        return $data;
    }
}
