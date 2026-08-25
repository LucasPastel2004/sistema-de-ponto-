<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\StatusJustificativa;
use App\Filament\Resources\JustificativaResource\Pages;
use App\Models\Justificativa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class JustificativaResource extends Resource
{
    protected static ?string $model = Justificativa::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Gestão de Ponto';

    protected static ?string $modelLabel = 'Justificativa';

    protected static ?string $pluralModelLabel = 'Justificativas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('colaborador_id')
                    ->relationship('colaborador', 'nome')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('ponto_id')
                    ->relationship('ponto', 'id')
                    ->nullable(),
                Forms\Components\DatePicker::make('data_referencia')
                    ->required(),
                Forms\Components\TextInput::make('tipo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descricao')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options(StatusJustificativa::class)
                    ->default(StatusJustificativa::Pendente)
                    ->disabledOn('create')
                    ->required(),
                Forms\Components\FileUpload::make('comprovante')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->maxSize(5120)
                    ->directory('comprovantes')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('colaborador.nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_referencia')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),
                Tables\Columns\TextColumn::make('aprovador.name')
                    ->label('Aprovador')
                    ->sortable(),
                Tables\Columns\TextColumn::make('aprovado_em')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StatusJustificativa::class),
                Tables\Filters\Filter::make('data_referencia')
                    ->form([
                        Forms\Components\DatePicker::make('de'),
                        Forms\Components\DatePicker::make('ate'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['de'],
                                fn ($q, $date) => $q->whereDate('data_referencia', '>=', $date),
                            )
                            ->when(
                                $data['ate'],
                                fn ($q, $date) => $q->whereDate('data_referencia', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('aprovar')
                        ->label('Aprovar Selecionadas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $aprovadas = 0;
                            foreach ($records as $r) {
                                // Verifica autorização individualmente (respeita Policy)
                                if (! auth()->user()->can('aprovar', $r)) {
                                    continue;
                                }
                                $r->update([
                                    'status' => StatusJustificativa::Aprovada,
                                    'aprovador_id' => auth()->id(),
                                    'aprovado_em' => now(),
                                ]);
                                $aprovadas++;
                            }
                            \Filament\Notifications\Notification::make()
                                ->title("{$aprovadas} justificativa(s) aprovada(s).")
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('rejeitar')
                        ->label('Rejeitar Selecionadas')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $rejeitadas = 0;
                            foreach ($records as $r) {
                                // Verifica autorização individualmente (respeita Policy)
                                if (! auth()->user()->can('rejeitar', $r)) {
                                    continue;
                                }
                                $r->update([
                                    'status' => StatusJustificativa::Rejeitada,
                                    'aprovador_id' => auth()->id(),
                                    'aprovado_em' => now(),
                                ]);
                                $rejeitadas++;
                            }
                            \Filament\Notifications\Notification::make()
                                ->title("{$rejeitadas} justificativa(s) rejeitada(s).")
                                ->danger()
                                ->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJustificativas::route('/'),
            'create' => Pages\CreateJustificativa::route('/create'),
            'edit' => Pages\EditJustificativa::route('/{record}/edit'),
        ];
    }
}
