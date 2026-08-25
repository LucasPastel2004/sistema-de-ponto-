<?php

declare(strict_types=1);

namespace App\Filament\Resources\PontoResource\Pages;

use App\Filament\Resources\PontoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPontos extends ListRecords
{
    protected static string $resource = PontoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => session('view_mode', 'admin') === 'admin' && (auth()->user()?->hasRole('admin') ?? false)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\BaterPontoWidget::class,
        ];
    }
}
