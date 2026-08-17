<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoPonto;
use App\Enums\MetodoValidacao;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Ponto extends Model
{
    use HasFactory;

    protected $fillable = [
        'colaborador_id',
        'tipo',
        'registrado_em',
        'latitude',
        'longitude',
        'ip_address',
        'device_info',
        'metodo_validacao',
        'observacao',
        'is_manual',
        'aprovado_por',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoPonto::class,
            'metodo_validacao' => MetodoValidacao::class,
            'registrado_em' => 'datetime',
            'device_info' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_manual' => 'boolean',
        ];
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function aprovadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprovado_por');
    }

    public function scopePorPeriodo(Builder $query, $inicio, $fim): Builder
    {
        return $query->whereBetween('registrado_em', [$inicio, $fim]);
    }

    public function scopePorColaborador(Builder $query, int $colaboradorId): Builder
    {
        return $query->where('colaborador_id', $colaboradorId);
    }

    public function scopeHoje(Builder $query): Builder
    {
        return $query->whereDate('registrado_em', today());
    }
}
