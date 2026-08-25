<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ColaboradorResource\Pages;
use App\Filament\Resources\ColaboradorResource\RelationManagers\PontosRelationManager;
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

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

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
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation !== 'create' || empty($state)) {
                                            return;
                                        }
                                        // "Joao da Silva" -> "joao.da.silva"
                                        $username = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '.', iconv('UTF-8', 'ASCII//TRANSLIT', $state)), '.'));
                                        $set('Tabs.Acesso (Login).username', $username); // Need to figure out state path... actually, just $set('username', $username) works for flat state usually.
                                        $set('username', $username);
                                    }),
                                Forms\Components\TextInput::make('cpf')
                                    ->required()
                                    ->maxLength(14)
                                    ->mask('999.999.999-99'),
                                Forms\Components\TextInput::make('cargo')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Forms\Components\Tabs\Tab::make('Vínculo')
                            ->schema([
                                Forms\Components\Select::make('empresa_id')
                                    ->relationship('empresa', 'razao_social')
                                    ->searchable()
                                    ->preload(false)
                                    ->required(),
                                Forms\Components\Select::make('departamento_id')
                                    ->relationship('departamento', 'nome')
                                    ->searchable()
                                    ->preload(false)
                                    ->required(),
                                Forms\Components\Select::make('escala_id')
                                    ->relationship('escala', 'nome')
                                    ->searchable()
                                    ->preload(false)
                                    ->required(),
                                Forms\Components\DatePicker::make('data_admissao')
                                    ->required(),
                                Forms\Components\DatePicker::make('data_demissao'),
                                Forms\Components\Toggle::make('ativo')
                                    ->default(true),
                            ]),
                        Forms\Components\Tabs\Tab::make('Acesso (Login)')
                            ->schema([
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->label('Nome de Usuário (Ex: joao.silva)')
                                    ->unique(table: 'users', column: 'username', ignorable: fn ($record) => $record?->user),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->label('E-mail (Opcional)')
                                    ->unique(table: 'users', column: 'email', ignorable: fn ($record) => $record?->user),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (?string $state) => filled($state))
                                    ->label('Senha (Digite para alterar)')
                                    ->maxLength(255),
                            ]),
                    ])->columnSpanFull(),
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
                Tables\Actions\Action::make('baixarEspelho')
                    ->label('Espelho (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('mes')
                            ->label('Mês')
                            ->options([
                                1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
                            ])
                            ->default(now()->month)
                            ->required(),
                        Forms\Components\Select::make('ano')
                            ->label('Ano')
                            ->options(
                                collect(range(now()->year - 2, now()->year))->mapWithKeys(fn ($year) => [$year => $year])->toArray()
                            )
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (Colaborador $record, array $data) {
                        $service = app(\App\Services\EspelhoPontoService::class);
                        $path = $service->gerarPdf($record->id, (int) $data['mes'], (int) $data['ano']);
                        return response()->download(public_path($path));
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PontosRelationManager::class,
        ];
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
