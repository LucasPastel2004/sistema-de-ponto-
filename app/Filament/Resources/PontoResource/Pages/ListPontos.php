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
            
            Actions\Action::make('exportar_afd')
                ->label('Exportar AFD (Portaria 671)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->visible(fn () => session('view_mode', 'admin') === 'admin' && (auth()->user()?->hasRole('admin') ?? false))
                ->form([
                    \Filament\Forms\Components\DatePicker::make('data_inicial')
                        ->label('Data Inicial')
                        ->required()
                        ->default(now()->startOfMonth()),
                    \Filament\Forms\Components\DatePicker::make('data_final')
                        ->label('Data Final')
                        ->required()
                        ->default(now()),
                    \Filament\Forms\Components\Select::make('empresa_id')
                        ->label('Empresa')
                        ->options(\App\Models\Empresa::ativas()->pluck('nome_fantasia', 'id'))
                        ->required()
                        ->default(fn () => \App\Models\Empresa::ativas()->first()->id ?? null),
                ])
                ->action(function (array $data) {
                    $service = app(\App\Services\ExportacaoAfdService::class);
                    $empresa = \App\Models\Empresa::find($data['empresa_id']);
                    
                    try {
                        $conteudoAfd = $service->gerarAfd($data['data_inicial'], $data['data_final'], $empresa);
                        
                        $nomeArquivo = 'AFD_' . $empresa->cnpj . '_' . date('YmdHis') . '.txt';
                        
                        return response()->streamDownload(function () use ($conteudoAfd) {
                            echo $conteudoAfd;
                        }, $nomeArquivo);
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Erro na Exportação')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\BaterPontoWidget::class,
        ];
    }
}
