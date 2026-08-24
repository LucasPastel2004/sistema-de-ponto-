<?php

namespace App\Filament\Resources\ColaboradorResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->color(fn (string $state): string => match ($state) {
                        'entrada' => 'success',
                        'saida' => 'danger',
                        'pausa' => 'warning',
                        'retorno' => 'info',
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
