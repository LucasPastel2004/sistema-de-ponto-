<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TipoEscala;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class EscalaTrabalho extends Model
{
    use HasFactory;

    protected $table = 'escalas_trabalho';

    protected $fillable = [
        'nome',
        'tipo',
        'carga_horaria_diaria',
        'tolerancia_minutos',
        'horario_entrada',
        'horario_saida',
        'intervalo_minutos',
        'dias_trabalho',
        'ativa',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => TipoEscala::class,
            'dias_trabalho' => 'array',
            'ativa' => 'boolean',
        ];
    }

    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'escala_id');
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativa', true);
    }
}
