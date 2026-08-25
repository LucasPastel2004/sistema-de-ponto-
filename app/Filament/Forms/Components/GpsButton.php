<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class GpsButton extends Placeholder
{
    public static function make(string $name = 'gps_button'): static
    {
        return parent::make($name)
            ->label('')
            ->content(function (\Filament\Forms\Components\Component $component) {
                $statePath = $component->getStatePath();
                // Determine the base path to set latitude/longitude
                $basePath = Str::contains($statePath, '.') 
                    ? Str::beforeLast($statePath, '.') 
                    : 'data';

                return new HtmlString(Blade::render('
                    <div x-data="{
                        loading: false,
                        getLocation() {
                            this.loading = true;
                            if (!navigator.geolocation) {
                                alert(\'Geolocalização não suportada no seu navegador.\');
                                this.loading = false;
                                return;
                            }
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    $wire.set(\'{{ $basePath }}.latitude\', pos.coords.latitude);
                                    $wire.set(\'{{ $basePath }}.longitude\', pos.coords.longitude);
                                    this.loading = false;
                                },
                                (err) => {
                                    alert(\'Erro ao buscar GPS: \' + err.message);
                                    this.loading = false;
                                },
                                { enableHighAccuracy: true }
                            );
                        }
                    }" class="pt-6">
                        <x-filament::button type="button" color="info" icon="heroicon-o-map-pin" x-on:click="getLocation()" x-bind:disabled="loading">
                            <span x-show="!loading">Capturar GPS Atual</span>
                            <span x-show="loading" x-cloak>Buscando...</span>
                        </x-filament::button>
                    </div>
                ', ['basePath' => $basePath]));
            });
    }
}
