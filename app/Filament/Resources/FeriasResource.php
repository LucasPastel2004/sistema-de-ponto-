<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FeriasResource\Pages;
use App\Models\Ferias;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FeriasResource extends Resource
{
    protected static ?string $model = Ferias::class;
    protected static ?string $navigationIcon = 'heroicon-o-sun';
    protected static ?string $navigationGroup = 'Gestão de Ponto';
    protected static ?string $navigationLabel = 'Férias';
    protected static ?string $modelLabel = 'Férias';
    protected static ?string $pluralModelLabel = 'Férias';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Definição do Período')
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('is_coletiva')
                        ->label('Férias Coletivas?')
                        ->default(false)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('colaborador_id', null);
                            } else {
                                $set('empresa_id', null);
                            }
                        })
                        ->helperText('Ative para definir férias para todos os colaboradores de uma empresa.')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('colaborador_id')
                        ->label('Colaborador')
                        ->relationship('colaborador', 'nome')
                        ->searchable()
                        ->preload()
                        ->required(fn (callable $get) => ! $get('is_coletiva'))
                        ->hidden(fn (callable $get) => $get('is_coletiva')),

                    Forms\Components\Select::make('empresa_id')
                        ->label('Empresa')
                        ->relationship('empresa', 'nome_fantasia')
                        ->searchable()
                        ->preload()
                        ->required(fn (callable $get) => $get('is_coletiva'))
                        ->hidden(fn (callable $get) => ! $get('is_coletiva'))
                        ->helperText('Todos os colaboradores desta empresa entrarão de férias neste período.'),

                    Forms\Components\DatePicker::make('data_inicio')
                        ->label('Data de Início')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Forms\Components\DatePicker::make('data_fim')
                        ->label('Data de Fim')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->afterOrEqual('data_inicio'),
                ]),

            Forms\Components\Section::make('Detalhes Adicionais')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('tipo')
                        ->label('Tipo de Férias')
                        ->options([
                            'integral' => 'Integral (30 dias)',
                            'parcial' => 'Parcial (Fracionado)',
                        ])
                        ->required()
                        ->default('integral'),

                    Forms\Components\TextInput::make('dias_vendidos')
                        ->label('Abono Pecuniário (Dias Vendidos)')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(10)
                        ->helperText('Máximo de 10 dias (1/3 das férias).'),

                    Forms\Components\Textarea::make('observacao')
                        ->label('Observações')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('alvo')
                    ->label('Colaborador / Coletiva')
                    ->getStateUsing(function (Ferias $record) {
                        if ($record->is_coletiva) {
                            return 'Coletiva: ' . ($record->empresa->nome_fantasia ?? 'N/A');
                        }
                        return $record->colaborador->nome ?? 'N/A';
                    })
                    ->searchable(['colaborador.nome', 'empresa.nome_fantasia'])
                    ->sortable(['colaborador_id', 'empresa_id']),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duracao_dias')
                    ->label('Duração (Dias)')
                    ->numeric()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('dias_vendidos')
                    ->label('Abono')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'integral' => 'success',
                        'parcial' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),
            ])
            ->defaultSort('data_inicio', 'desc')
            ->filters([
                Tables\Filters\Filter::make('apenas_vigentes')
                    ->label('Apenas Vigentes')
                    ->query(fn (Forms\Builder $query) => $query->vigentes()),
                    
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'integral' => 'Integral',
                        'parcial' => 'Parcial',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFerias::route('/'),
            'create' => Pages\CreateFerias::route('/create'),
            'edit' => Pages\EditFerias::route('/{record}/edit'),
        ];
    }
}
