<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'inscricao_estadual',
        'endereco',
        'telefone',
        'email',
        'latitude',
        'longitude',
        'raio_ponto_metros',
        'ativa',
    ];

    protected function casts(): array
    {
        return [
            'cnpj' => 'encrypted',
            'endereco' => 'array',
            'ativa' => 'boolean',
        ];
    }

    public function departamentos(): HasMany
    {
        return $this->hasMany(Departamento::class);
    }

    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class);
    }

    public function feriados(): HasMany
    {
        return $this->hasMany(Feriado::class);
    }

    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where('ativa', true);
    }
}
