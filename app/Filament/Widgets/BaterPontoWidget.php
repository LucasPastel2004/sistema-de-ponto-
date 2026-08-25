<?php

declare(strict_types=1);

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
    
    protected static bool $isLazy = true;

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->colaborador !== null;
    }

    public function registrarPonto($latitude, $longitude): void
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

            // Determinar o próximo tipo de ponto com base no último registrado hoje.
            // Fluxo esperado: Entrada → IntervaloInicio → IntervaloFim → Saída → (novo ciclo)
            $ultimoPonto = Ponto::where('colaborador_id', $user->colaborador->id)
                ->whereDate('registrado_em', Carbon::today())
                ->orderBy('registrado_em', 'desc')
                ->first();

            $tipo = $this->determinarProximoTipo($ultimoPonto?->tipo);

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
                ->body('Seu ponto de '.$tipo->label().' foi registrado com sucesso às '.now()->format('H:i:s'))
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

    /**
     * Determina o próximo tipo de ponto com base no último registrado.
     * Fluxo: Entrada → IntervaloInicio → IntervaloFim → Saída → Entrada (novo ciclo).
     */
    private function determinarProximoTipo(?TipoPonto $ultimoTipo): TipoPonto
    {
        return match ($ultimoTipo) {
            TipoPonto::Entrada       => TipoPonto::IntervaloInicio,
            TipoPonto::IntervaloInicio => TipoPonto::IntervaloFim,
            TipoPonto::IntervaloFim  => TipoPonto::Saida,
            TipoPonto::Saida         => TipoPonto::Entrada, // Novo ciclo (ex: hora extra)
            null                     => TipoPonto::Entrada, // Primeiro ponto do dia
        };
    }
}
