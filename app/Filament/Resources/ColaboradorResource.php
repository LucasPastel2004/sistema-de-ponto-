<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ColaboradorResource\Pages;
use App\Models\Colaborador;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ColaboradorResource extends Resource
{
    protected static ?string $model = Colaborador::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Colaborador';

    protected static ?string $pluralModelLabel = 'Colaboradores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Dados Pessoais')
                            ->schema([
                                Forms\Components\TextInput::make('nome')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('cpf')
                                    ->required()
                                    ->maxLength(14)
                                    ->mask('999.999.999-99'),
                                Forms\Components\TextInput::make('matricula')
                                    ->required()
                                    ->maxLength(50),
                                Forms\Components\TextInput::make('cargo')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Tabs\Tab::make('Vínculo')
                            ->schema([
                                Forms\Components\Select::make('empresa_id')
                                    ->relationship('empresa', 'razao_social')
                                    ->required(),
                                Forms\Components\Select::make('departamento_id')
                                    ->relationship('departamento', 'nome')
                                    ->required(),
                                Forms\Components\Select::make('escala_id')
                                    ->relationship('escala', 'nome')
                                    ->required(),
                                Forms\Components\DatePicker::make('data_admissao')
                                    ->required(),
                                Forms\Components\DatePicker::make('data_demissao'),
                                Forms\Components\Toggle::make('ativo')
                                    ->default(true),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('matricula')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('empresa.razao_social')
                    ->sortable(),
                Tables\Columns\TextColumn::make('departamento.nome')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cargo')
                    ->searchable(),
                Tables\Columns\IconColumn::make('ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('empresa_id')
                    ->relationship('empresa', 'razao_social')
                    ->label('Empresa'),
                Tables\Filters\TernaryFilter::make('ativo'),
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
            'index' => Pages\ListColaboradores::route('/'),
            'create' => Pages\CreateColaborador::route('/create'),
            'edit' => Pages\EditColaborador::route('/{record}/edit'),
        ];
    }
}
