<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Colaborador;
use App\Models\Ponto;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlertasOmissaoWidget extends BaseWidget
{
    public function table(Table $table): Table
    {
        $hoje = today();
        $colaboradoresComPonto = Ponto::whereDate('registrado_em', $hoje)->pluck('colaborador_id');

        return $table
            ->query(
                Colaborador::query()
                    ->where('ativo', true)
                    ->whereNotIn('id', $colaboradoresComPonto)
                    ->orderBy('nome')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('matricula')
                    ->label('Matrícula'),
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome'),
                Tables\Columns\TextColumn::make('departamento.nome')
                    ->label('Departamento'),
                Tables\Columns\TextColumn::make('ultimo_ponto')
                    ->label('Último Ponto')
                    ->getStateUsing(function (Colaborador $record) {
                        $ponto = Ponto::where('colaborador_id', $record->id)
                            ->orderBy('registrado_em', 'desc')
                            ->first();
                        return $ponto ? $ponto->registrado_em->format('d/m/Y H:i') : 'Sem registro';
                    }),
            ]);
    }
}
