<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Colaborador extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'colaboradores';

    protected $fillable = [
        'user_id',
        'empresa_id',
        'departamento_id',
        'escala_id',
        'nome',
        'cpf',
        'cpf_hash',
        'matricula',
        'cargo',
        'data_admissao',
        'data_demissao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'cpf' => 'encrypted',
            'data_admissao' => 'date',
            'data_demissao' => 'date',
            'ativo' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Colaborador $colaborador) {
            if ($colaborador->isDirty('cpf') && $colaborador->cpf) {
                $colaborador->cpf_hash = hash('sha256', $colaborador->cpf);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(EscalaTrabalho::class, 'escala_id');
    }

    public function pontos(): HasMany
    {
        return $this->hasMany(Ponto::class);
    }

    public function justificativas(): HasMany
    {
        return $this->hasMany(Justificativa::class);
    }

    public function scopeAtivos(Builder $query): Builder
    {
        return $query->where('ativo', true);
    }

    public function getFullIdentifier(): string
    {
        return "{$this->matricula} - {$this->nome}";
    }
}
