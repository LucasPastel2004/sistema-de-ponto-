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
    protected static ?string $pollingInterval = '60s';

    public function table(Table $table): Table
    {
        $hoje = today();

        return $table
            ->query(
                Colaborador::query()
                    ->with('departamento')
                    ->where('ativo', true)
                    ->whereDoesntHave('pontos', function ($q) use ($hoje) {
                        $q->whereBetween('registrado_em', [$hoje->copy()->startOfDay(), $hoje->copy()->endOfDay()]);
                    })
                    ->addSelect([
                        'ultimo_ponto_data' => Ponto::select('registrado_em')
                            ->whereColumn('colaborador_id', 'colaboradores.id')
                            ->orderBy('registrado_em', 'desc')
                            ->limit(1)
                    ])
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
                        return $record->ultimo_ponto_data 
                            ? \Carbon\Carbon::parse($record->ultimo_ponto_data)->format('d/m/Y H:i') 
                            : 'Sem registro';
                    }),
            ]);
    }
}
