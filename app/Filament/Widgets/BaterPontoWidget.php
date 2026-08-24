<?php

namespace App\Filament\Widgets;

use App\DTOs\PontoData;
use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use App\Models\Ponto;
use App\Services\RegistroPontoService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class BaterPontoWidget extends Widget
{
    protected static string $view = 'filament.widgets.bater-ponto-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->colaborador !== null;
    }

    public function registrarPonto($latitude, $longitude)
    {
        $user = auth()->user();
        if (! $user->colaborador) {
            Notification::make()
                ->title('Erro')
                ->body('Seu usuário não está vinculado a nenhum colaborador. Contate o RH.')
                ->danger()
                ->send();

            return;
        }

        try {
            $service = app(RegistroPontoService::class);

            // Determinar o tipo de ponto (Entrada ou Saída)
            $ultimoPonto = Ponto::where('colaborador_id', $user->colaborador->id)
                ->whereDate('registrado_em', Carbon::today())
                ->orderBy('registrado_em', 'desc')
                ->first();

            $tipo = TipoPonto::Entrada;
            if ($ultimoPonto) {
                // Alterna entre entrada e saída
                $tipo = $ultimoPonto->tipo === TipoPonto::Entrada ? TipoPonto::Saida : TipoPonto::Entrada;
            }

            $dto = new PontoData(
                colaborador_id: $user->colaborador->id,
                registrado_em: now(),
                tipo: $tipo,
                is_manual: false,
                metodo_validacao: MetodoValidacao::GPS,
                latitude: $latitude ? (float) $latitude : null,
                longitude: $longitude ? (float) $longitude : null,
                ip_address: request()->ip()
            );

            $service->registrar($dto);

            Notification::make()
                ->title('Ponto Registrado!')
                ->body('Seu ponto de '.$tipo->value.' foi registrado com sucesso às '.now()->format('H:i:s'))
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Falha ao Registrar')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
