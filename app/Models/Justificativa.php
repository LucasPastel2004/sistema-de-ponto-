<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusJustificativa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Justificativa extends Model
{
    use HasFactory;

    protected $fillable = [
        'colaborador_id',
        'ponto_id',
        'data_referencia',
        'tipo',
        'descricao',
        'comprovante_path',
        'status',
        'aprovador_id',
        'aprovado_em',
        'observacao_aprovador',
    ];

    protected function casts(): array
    {
        return [
            'status' => StatusJustificativa::class,
            'data_referencia' => 'date',
            'aprovado_em' => 'datetime',
        ];
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function ponto(): BelongsTo
    {
        return $this->belongsTo(Ponto::class);
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }

    public function scopePendentes(Builder $query): Builder
    {
        return $query->where('status', StatusJustificativa::Pendente);
    }

    public function scopePorPeriodo(Builder $query, $inicio, $fim): Builder
    {
        return $query->whereBetween('data_referencia', [$inicio, $fim]);
    }

    public function isPendente(): bool
    {
        return $this->status === StatusJustificativa::Pendente;
    }

    public function isAprovada(): bool
    {
        return $this->status === StatusJustificativa::Aprovada;
    }
}
