<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Feriado extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'nome',
        'data',
        'tipo',
        'recorrente',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
            'recorrente' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeNacionais(Builder $query): Builder
    {
        return $query->where('tipo', 'nacional');
    }

    public function scopePorEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
    }

    public function scopeDoAno(Builder $query, int $ano): Builder
    {
        return $query->whereYear('data', $ano);
    }
}
