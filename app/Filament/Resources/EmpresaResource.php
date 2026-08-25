<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Empresas';

    public static function canViewAny(): bool
    {
        if (session('view_mode', 'admin') !== 'admin') {
            return false;
        }
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('razao_social')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nome_fantasia')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cnpj')
                    ->required()
                    ->maxLength(18)
                    ->mask('99.999.999/9999-99'),
                Forms\Components\TextInput::make('inscricao_estadual')
                    ->maxLength(50),
                Forms\Components\TextInput::make('telefone')
                    ->tel()
                    ->maxLength(20),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\Fieldset::make('Endereço')
                    ->statePath('endereco')
                    ->schema([
                        Forms\Components\TextInput::make('cep')
                            ->label('CEP')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('logradouro')
                            ->label('Logradouro (Rua/Av)')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->maxLength(50),
                        Forms\Components\TextInput::make('complemento')
                            ->label('Complemento')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bairro')
                            ->label('Bairro')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cidade')
                            ->label('Cidade')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('uf')
                            ->label('UF')
                            ->maxLength(2),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
                Forms\Components\Fieldset::make('Cerca Virtual - Matriz (Opcional)')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->helperText('Ex: -23.550520')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->helperText('Ex: -46.633308')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('raio_ponto_metros')
                            ->label('Raio Permitido (metros)')
                            ->numeric()
                            ->default(20)
                            ->helperText('Distância máxima permitida.'),
                    ]),
                Forms\Components\Repeater::make('polos')
                    ->label('Outros Polos / Filiais (Opcional)')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome do Polo')
                            ->required(),
                        Forms\Components\TextInput::make('latitude')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('longitude')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('raio_ponto_metros')
                            ->label('Raio (metros)')
                            ->numeric()
                            ->default(20)
                            ->required(),
                    ])
                    ->columns(4)
                    ->columnSpanFull()
                    ->helperText('Adicione polos adicionais onde o funcionário também está autorizado a bater o ponto.'),
                Forms\Components\Toggle::make('bloqueia_ponto_fora_horario')
                    ->label('Bloquear Ponto Fora do Horário')
                    ->default(false)
                    ->helperText('Se ativado, impede a marcação se o funcionário estiver com atraso ou adiantamento além da tolerância.'),
                Forms\Components\Toggle::make('ativa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('razao_social')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome_fantasia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cnpj')
                    ->searchable(),
                Tables\Columns\IconColumn::make('ativa')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}
