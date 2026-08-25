<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use HasFactory, SoftDeletes;

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
        'polos',
        'bloqueia_ponto_fora_horario',
        'ativa',
    ];

    protected function casts(): array
    {
        return [
            'cnpj' => 'encrypted',
            'endereco' => 'array',
            'polos' => 'array',
            'ativa' => 'boolean',
            'bloqueia_ponto_fora_horario' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (Empresa $empresa) {
            // Desativa os colaboradores vinculados quando a empresa é excluída (soft delete)
            if (! $empresa->isForceDeleting()) {
                $empresa->colaboradores()->update([
                    'ativo' => false,
                    'data_demissao' => now(),
                ]);
            }
        });
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
