<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\FeriadoResource\Pages;
use App\Models\Feriado;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class FeriadoResource extends Resource
{
    protected static ?string $model = Feriado::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Feriados';
    protected static ?string $modelLabel = 'Feriado';
    protected static ?string $pluralModelLabel = 'Feriados';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados do Feriado')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('nome')
                        ->label('Nome do Feriado')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ex: Natal, Dia da Consciência Negra'),

                    Forms\Components\DatePicker::make('data')
                        ->label('Data')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y'),

                    Forms\Components\Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            'nacional' => 'Nacional',
                            'estadual' => 'Estadual',
                            'municipal' => 'Municipal',
                            'empresa' => 'Interno da Empresa',
                        ])
                        ->required()
                        ->default('nacional'),

                    Forms\Components\Select::make('empresa_id')
                        ->label('Empresa (opcional)')
                        ->relationship('empresa', 'nome_fantasia')
                        ->placeholder('Todas as empresas')
                        ->searchable()
                        ->preload()
                        ->helperText('Deixe vazio para aplicar a todas as empresas.'),

                    Forms\Components\Toggle::make('recorrente')
                        ->label('Feriado Recorrente (repete todo ano)')
                        ->default(false)
                        ->helperText('Se ativado, o feriado será considerado no mesmo dia/mês em todos os anos.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Feriado')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nacional' => 'success',
                        'estadual' => 'info',
                        'municipal' => 'warning',
                        'empresa' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('empresa.nome_fantasia')
                    ->label('Empresa')
                    ->default('Todas')
                    ->sortable(),

                Tables\Columns\IconColumn::make('recorrente')
                    ->label('Recorrente')
                    ->boolean(),
            ])
            ->defaultSort('data', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'nacional' => 'Nacional',
                        'estadual' => 'Estadual',
                        'municipal' => 'Municipal',
                        'empresa' => 'Empresa',
                    ]),
                Tables\Filters\Filter::make('ano')
                    ->form([
                        Forms\Components\Select::make('ano')
                            ->options(fn () => collect(range(now()->year - 1, now()->year + 2))
                                ->mapWithKeys(fn ($y) => [$y => $y])
                                ->toArray())
                            ->default(now()->year),
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['ano']) {
                            $query->whereYear('data', $data['ano']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importar_nacionais')
                    ->label('Importar Feriados Nacionais')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('ano')
                            ->label('Ano')
                            ->options(fn () => collect(range(now()->year, now()->year + 2))
                                ->mapWithKeys(fn ($y) => [$y => $y])
                                ->toArray())
                            ->default(now()->year)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        Artisan::call('feriados:importar', ['ano' => $data['ano']]);
                        $output = Artisan::output();

                        Notification::make()
                            ->title('Importação Concluída')
                            ->body($output)
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeriados::route('/'),
            'create' => Pages\CreateFeriado::route('/create'),
            'edit' => Pages\EditFeriado::route('/{record}/edit'),
        ];
    }
}
