<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\MetodoValidacao;
use App\Enums\TipoPonto;
use App\Filament\Resources\PontoResource\Pages;
use App\Models\Ponto;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PontoResource extends Resource
{
    protected static ?string $model = Ponto::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Gestão de Ponto';

    protected static ?string $modelLabel = 'Registro de Ponto';

    protected static ?string $pluralModelLabel = 'Registros de Ponto';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('colaborador_id')
                    ->relationship('colaborador', 'nome')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('tipo')
                    ->options(TipoPonto::class)
                    ->required(),
                Forms\Components\DateTimePicker::make('registrado_em')
                    ->required(),
                Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\Select::make('metodo_validacao')
                    ->options(MetodoValidacao::class)
                    ->required(),
                Forms\Components\Textarea::make('observacao')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_manual')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('registrado_em', 'desc')
            ->modifyQueryUsing(fn ($query) => $query->select([
                'id',
                'colaborador_id',
                'tipo',
                'registrado_em',
                'metodo_validacao',
                'is_manual'
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('colaborador.nome')
                    ->sortable()
                    ->searchable(false),
                Tables\Columns\TextColumn::make('tipo')
                    ->badge(),
                Tables\Columns\TextColumn::make('registrado_em')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('metodo_validacao'),
                Tables\Columns\IconColumn::make('is_manual')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options(TipoPonto::class),
                Tables\Filters\SelectFilter::make('metodo_validacao')
                    ->options(MetodoValidacao::class),
                Tables\Filters\Filter::make('registrado_em')
                    ->form([
                        Forms\Components\DatePicker::make('registrado_de'),
                        Forms\Components\DatePicker::make('registrado_ate'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['registrado_de'],
                                fn ($q, $date) => $q->where('registrado_em', '>=', Carbon::parse($date)->startOfDay()),
                            )
                            ->when(
                                $data['registrado_ate'],
                                fn ($q, $date) => $q->where('registrado_em', '<=', Carbon::parse($date)->endOfDay()),
                            );
                    }),
                Tables\Filters\SelectFilter::make('colaborador_id')
                    ->relationship('colaborador', 'nome')
                    ->searchable()
                    ->label('Colaborador'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPontos::route('/'),
            'create' => Pages\CreatePonto::route('/create'),
            'edit' => Pages\EditPonto::route('/{record}/edit'),
        ];
    }
}
