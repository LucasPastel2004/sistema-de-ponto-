<?php

namespace App\Filament\Resources\ColaboradorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BancoHorasLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'bancoHorasLogs';
    protected static ?string $title = 'Extrato do Banco de Horas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('data_referencia')
                    ->label('Data Referência')
                    ->required(),
                Forms\Components\TextInput::make('minutos_ajuste')
                    ->label('Ajuste em Minutos (Ex: -60 ou 120)')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('observacao')
                    ->label('Observação')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('data_referencia')
            ->defaultSort('data_referencia', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('data_referencia')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('minutos_ajuste')
                    ->label('Ajuste Aplicado')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (int $state) {
                        $sinal = $state < 0 ? '-' : '+';
                        $minutosAbs = abs($state);
                        $horas = floor($minutosAbs / 60);
                        $minutos = $minutosAbs % 60;
                        return sprintf('%s %02dh %02dm', $sinal, $horas, $minutos);
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('observacao')
                    ->label('Observação'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Desabilitado, os logs devem ser criados via cálculo do sistema
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
