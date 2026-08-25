<?php

namespace App\Filament\Resources\ColaboradorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PontosRelationManager extends RelationManager
{
    protected static string $relationship = 'pontos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('data_hora')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('registrado_em')
            ->defaultSort('registrado_em', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->badge()
                    ->color(fn (\App\Enums\TipoPonto $state): string => match ($state) {
                        \App\Enums\TipoPonto::Entrada => 'success',
                        \App\Enums\TipoPonto::Saida => 'danger',
                        \App\Enums\TipoPonto::IntervaloInicio => 'warning',
                        \App\Enums\TipoPonto::IntervaloFim => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('registrado_em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_manual')
                    ->label('Manual')
                    ->boolean(),
                Tables\Columns\TextColumn::make('metodo_validacao')
                    ->label('Validação'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Read-only overview mostly
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
